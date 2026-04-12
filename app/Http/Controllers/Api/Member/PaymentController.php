<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\MemberRoutine;
use App\Models\WorkoutRoutine;
use App\Models\MemberMeal;
use App\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class PaymentController extends Controller
{
    /**
     * GET /member/plans?goal=cutting|bulking
     * Returns: { plans: { bronze:{price,plan_key,goal,currency}, ... } }
     */
    public function plans(Request $request)
    {
        $data = $request->validate([
            'goal' => 'required|in:cutting,bulking',
        ]);

        $goalPlans = config('plans.' . $data['goal']);

        if (!$goalPlans || !is_array($goalPlans)) {
            return response()->json([
                'message' => 'Invalid goal configuration.',
            ], 422);
        }

        $result = [];

        foreach ($goalPlans as $tier => $p) {
            $result[$tier] = [
                'plan_key' => $tier,
                'goal' => $data['goal'],
                'name' => ucfirst($tier),
                'price' => $p['price'] ?? 0,
                'currency' => 'USD',
            ];
        }

        return response()->json([
            'plans' => $result,
        ]);
    }

    /**
     * POST /member/subscribe
     * Body: { goal:cutting|bulking, plan_key:bronze|silver|gold, payment_method:... }
     */
    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'goal' => 'required|in:cutting,bulking',
            'plan_key' => 'required|in:bronze,silver,gold',
            'payment_method' => 'required|in:cash,credit_card,debit_card,bank_transfer,digital_wallet',
        ]);

        $profile = $request->user()->profile;

        if (!$profile) {
            return response()->json([
                'message' => 'Profile not found.',
            ], 404);
        }

        $profileId = $profile->id;

        // ✅ جلب الخطة من config/plans.php حسب الهدف + الباقة
        $plan = config('plans.' . $data['goal'] . '.' . $data['plan_key']);

        if (!$plan || !is_array($plan)) {
            return response()->json([
                'message' => 'Selected plan is missing in configuration. Payment aborted.',
            ], 422);
        }

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        return DB::transaction(function () use ($data, $profile, $profileId, $plan, $days) {
            $startDate = now()->toDateString();
            $endDate = now()->addMonth()->toDateString();

            // ===== 1) ✅ تحقق من الروتينات المطلوبة بالخطة قبل أي عملية دفع =====
            $routineIds = Arr::wrap($plan['routines'] ?? []);

            if (count($routineIds) === 0) {
                return response()->json([
                    'message' => 'Plan has no routines configured. Payment aborted.',
                ], 422);
            }

            $foundRoutineIds = WorkoutRoutine::whereIn('id', $routineIds)->pluck('id')->all();
            $missingRoutines = array_values(array_diff($routineIds, $foundRoutineIds));

            if (!empty($missingRoutines)) {
                return response()->json([
                    'message' => 'Plan routines missing in DB. Payment aborted.',
                    'missing_routine_ids' => $missingRoutines,
                ], 422);
            }

            // ===== 2) ✅ تحقق من الوجبات المطلوبة بالخطة قبل أي عملية دفع =====
            $planMeals = $plan['meals'] ?? [];

            if (count($planMeals) === 0) {
                return response()->json([
                    'message' => 'Plan has no meals configured. Payment aborted.',
                ], 422);
            }

            $mealIds = array_values(array_unique(array_map(
                fn ($m) => $m['meal_id'] ?? null,
                $planMeals
            )));

            $mealIds = array_values(array_filter($mealIds));

            if (count($mealIds) === 0) {
                return response()->json([
                    'message' => 'Plan meals configuration is invalid. Payment aborted.',
                ], 422);
            }

            $foundMealIds = Meal::whereIn('id', $mealIds)->pluck('id')->all();
            $missingMeals = array_values(array_diff($mealIds, $foundMealIds));

            if (!empty($missingMeals)) {
                return response()->json([
                    'message' => 'Plan meals missing in DB. Payment aborted.',
                    'missing_meal_ids' => $missingMeals,
                ], 422);
            }

            // ===== 3) ✅ حدّث حالة اشتراك العضو =====
            if ($profile->memberProfile) {
                $profile->memberProfile->update([
                    'membership_tier' => $data['plan_key'],
                    'membership_expires_at' => $endDate,
                    // 'goal' => $data['goal'], // فعّلها إذا عندك العمود
                ]);
            }

            // ===== 4) ✅ أرشف/عطّل القديم =====
            MemberRoutine::where('member_id', $profileId)->update([
                'status' => 'archived',
            ]);

            MemberMeal::where('member_id', $profileId)->update([
                'is_active' => 0,
            ]);

            // ===== 5) ✅ اسند الروتينات للعضو =====
            foreach ($routineIds as $rid) {
                MemberRoutine::create([
                    'member_id' => $profileId,
                    'routine_id' => $rid,
                    'assigned_by' => null,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'active',
                ]);
            }

            // ===== 6) ✅ اسند الوجبات للعضو =====
            $mealsById = Meal::whereIn('id', $mealIds)->get()->keyBy('id');

            $dayIndex = 0;

            foreach ($planMeals as $row) {
                $meal = $mealsById[$row['meal_id']] ?? null;
                $assignedDay = $row['day_of_week'] ?? $days[$dayIndex % count($days)];
                $dayIndex++;

                MemberMeal::create([
                    'member_id' => $profileId,
                    'meal_id' => $row['meal_id'],
                    'assigned_by' => $meal?->trainer_id,
                    'meal_time' => $row['meal_time'] ?? null,
                    'day_of_week' => $assignedDay,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'is_active' => 1,
                ]);
            }

            // ===== 7) ✅ سجل الدفع آخر شيء فقط بعد نجاح كل شيء =====
            $payment = Payment::create([
                'user_id' => $profileId, // FK -> profiles.id
                'amount' => $plan['price'] ?? 0,
                'payment_type' => 'membership',
                'payment_method' => $data['payment_method'],
                'status' => 'completed',
                'payment_date' => now(),
                'notes' => 'Subscription: ' . $data['goal'] . '.' . $data['plan_key'],
                'processed_by' => null,
            ]);

            return response()->json([
                'message' => 'Subscribed successfully (goal-based)',
                'goal' => $data['goal'],
                'plan_key' => $data['plan_key'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'payment' => $payment,
                'assigned' => [
                    'workouts_count' => count($routineIds),
                    'meals_count' => count($planMeals),
                ],
            ], 201);
        });
    }

    public function myPayments(Request $request)
    {
        $profile = $request->user()->profile;

        if (!$profile) {
            return response()->json([
                'message' => 'Profile not found.',
            ], 404);
        }

        $items = Payment::query()
            ->where('user_id', $profile->id)
            ->orderByDesc('payment_date')
            ->paginate(20);

        return response()->json($items);
    }

    /**
     * GET /member/plan-details?goal=cutting|bulking
     * - إذا بعت goal: بيرجع تفاصيل tiers لهذا الهدف
     * - إذا ما بعت: بيرجع تفاصيل كل الأهداف
     */
    public function planDetails(Request $request)
    {
        $goal = $request->query('goal');

        if ($goal) {
            $request->validate([
                'goal' => 'in:cutting,bulking',
            ]);
        }

        $plans = $goal ? config('plans.' . $goal) : config('plans');

        $result = [];

        $isMultiGoal = $goal ? false : true;

        if ($isMultiGoal) {
            foreach ($plans as $g => $tiers) {
                $result[$g] = $this->buildGoalPlanDetails($tiers);
            }

            return response()->json($result);
        }

        $result = $this->buildGoalPlanDetails($plans);

        return response()->json($result);
    }

    /**
     * Helper: builds details for tiers inside ONE goal
     */
    private function buildGoalPlanDetails(array $tiers)
    {
        $out = [];

        foreach ($tiers as $key => $p) {
            $routineIds = $p['routines'] ?? [];
            $mealRows = $p['meals'] ?? [];

            $routines = WorkoutRoutine::whereIn('id', $routineIds)
                ->get(['id', 'name'])
                ->map(fn ($r) => [
                    'id' => $r->id,
                    'name' => $r->name,
                ])
                ->values();

            $mealIds = collect($mealRows)->pluck('meal_id')->unique()->values()->all();

            $mealsById = Meal::whereIn('id', $mealIds)
                ->get(['id', 'name'])
                ->keyBy('id');

            $meals = collect($mealRows)->map(function ($row) use ($mealsById) {
                $m = $mealsById[$row['meal_id']] ?? null;

                return [
                    'meal_id' => $row['meal_id'],
                    'name' => $m?->name,
                    'meal_time' => $row['meal_time'] ?? null,
                    'day_of_week' => $row['day_of_week'] ?? null,
                ];
            })->values();

            $out[$key] = [
                'price' => $p['price'] ?? 0,
                'routines' => $routines,
                'meals' => $meals,
            ];
        }

        return $out;
    }
}
