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
            return response()->json(['message' => 'Invalid goal configuration.'], 422);
        }

        $result = [];
        foreach ($goalPlans as $tier => $p) {
            $result[$tier] = [
                'plan_key'  => $tier,                 // bronze/silver/gold
                'goal'      => $data['goal'],         // cutting/bulking
                'name'      => ucfirst($tier),
                'price'     => $p['price'] ?? 0,
                'currency'  => 'USD',
            ];
        }

        return response()->json(['plans' => $result]);
    }

    /**
     * POST /member/subscribe
     * Body: { goal:cutting|bulking, plan_key:bronze|silver|gold, payment_method:... }
     */
    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'goal'           => 'required|in:cutting,bulking',
            'plan_key'       => 'required|in:bronze,silver,gold',
            'payment_method' => 'required|in:cash,credit_card,debit_card,bank_transfer,digital_wallet',
        ]);

        $profile   = $request->user()->profile;
        $profileId = $profile->id;

        // ✅ جلب الخطة من config/plans.php حسب الهدف + الباقة
        $plan = config('plans.' . $data['goal'] . '.' . $data['plan_key']);
        if (!$plan) {
            return response()->json(['message' => 'Invalid plan configuration.'], 422);
        }

        $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

        return DB::transaction(function () use ($data, $profile, $profileId, $plan, $days) {

            // ===== 1) ✅ سجل الدفع =====
            $payment = Payment::create([
                'user_id'        => $profileId, // FK -> profiles.id (اسمها user_id لكنها profile فعلياً)
                'amount'         => $plan['price'],
                'payment_type'   => 'membership',
                'payment_method' => $data['payment_method'],
                'status'         => 'completed',
                'payment_date'   => now(),
                'notes'          => 'Subscription: ' . $data['goal'] . '.' . $data['plan_key'],
                'processed_by'   => null,
            ]);

            // ===== 2) ✅ تحديث tier + goal (إذا عندك حقل للهدف) =====
            if ($profile->memberProfile) {
                // لو عندك عمود goal بالـ member_profiles ضيفه، إذا ما عندك احذف سطر goal
                $profile->memberProfile->update([
                    'membership_tier' => $data['plan_key'],
                    // 'goal' => $data['goal'], // فعّلها إذا عندك هذا العمود
                ]);
            }

            // ===== 3) ✅ أرشف/عطّل القديم =====
            MemberRoutine::where('member_id', $profileId)->update(['status' => 'archived']);
            MemberMeal::where('member_id', $profileId)->update(['is_active' => 0]);

            // ===== 4) ✅ تحقق من الروتينات المطلوبة بالخطة =====
            $routineIds = Arr::wrap($plan['routines'] ?? []);
            if (count($routineIds) === 0) {
                return response()->json(['message' => 'Plan has no routines configured.'], 422);
            }

            $foundRoutineIds = WorkoutRoutine::whereIn('id', $routineIds)->pluck('id')->all();
            $missingRoutines = array_values(array_diff($routineIds, $foundRoutineIds));
            if (!empty($missingRoutines)) {
                return response()->json([
                    'message' => 'Plan routines missing in DB.',
                    'missing_routine_ids' => $missingRoutines,
                ], 422);
            }

            // ===== 5) ✅ تحقق من الوجبات المطلوبة بالخطة =====
            $planMeals = $plan['meals'] ?? [];
            if (count($planMeals) === 0) {
                return response()->json(['message' => 'Plan has no meals configured.'], 422);
            }

            $mealIds = array_values(array_unique(array_map(fn ($m) => $m['meal_id'], $planMeals)));

            $foundMealIds = Meal::whereIn('id', $mealIds)->pluck('id')->all();
            $missingMeals = array_values(array_diff($mealIds, $foundMealIds));
            if (!empty($missingMeals)) {
                return response()->json([
                    'message' => 'Plan meals missing in DB.',
                    'missing_meal_ids' => $missingMeals,
                ], 422);
            }

            // ===== 6) ✅ اسند الروتينات للعضو (Snapshot) =====
            foreach ($routineIds as $rid) {
                MemberRoutine::create([
                    'member_id'   => $profileId,
                    'routine_id'  => $rid,
                    'assigned_by' => null,
                    'start_date'  => now()->toDateString(),
                    'status'      => 'active',
                ]);
            }

            // ===== 7) ✅ اسند الوجبات للعضو (Snapshot) + day_of_week =====
            $mealsById = Meal::whereIn('id', $mealIds)->get()->keyBy('id');

            // توزيع الأيام: Monday..Sunday وبعدين نرجع نلف لو في أكثر من 7
            $dayIndex = 0;

            foreach ($planMeals as $row) {
                $meal = $mealsById[$row['meal_id']] ?? null;

                $assignedDay = $row['day_of_week'] ?? $days[$dayIndex % count($days)];
                $dayIndex++;

                MemberMeal::create([
                    'member_id'   => $profileId,
                    'meal_id'     => $row['meal_id'],
                    'assigned_by' => $meal?->trainer_id,
                    'meal_time'   => $row['meal_time'] ?? null,
                    'day_of_week' => $assignedDay,
                    'start_date'  => now()->toDateString(),
                    'is_active'   => 1,
                ]);
            }

            return response()->json([
                'message'  => 'Subscribed successfully (goal-based)',
                'goal'     => $data['goal'],
                'plan_key' => $data['plan_key'],
                'payment'  => $payment,
                'assigned' => [
                    'workouts_count' => count($routineIds),
                    'meals_count'    => count($planMeals),
                ],
            ], 201);
        });
    }

    public function myPayments(Request $request)
    {
        $profile = $request->user()->profile;

        $items = Payment::query()
            ->where('user_id', $profile->id)
            ->orderByDesc('payment_date')
            ->paginate(20);

        return response()->json($items);
    }

    /**
     * GET /member/plan-details?goal=cutting|bulking (اختياري)
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

        // إذا جبت كل الأهداف: $plans = ['cutting'=>[tiers], 'bulking'=>[tiers]]
        // إذا جبت هدف واحد: $plans = [tiers]
        $isMultiGoal = $goal ? false : true;

        if ($isMultiGoal) {
            foreach ($plans as $g => $tiers) {
                $result[$g] = $this->buildGoalPlanDetails($tiers);
            }
            return response()->json($result);
        }

        // هدف واحد فقط
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
            $mealRows   = $p['meals'] ?? [];

            $routines = WorkoutRoutine::whereIn('id', $routineIds)
                ->get(['id', 'name'])
                ->map(fn ($r) => ['id' => $r->id, 'name' => $r->name])
                ->values();

            $mealIds = collect($mealRows)->pluck('meal_id')->unique()->values()->all();
            $mealsById = Meal::whereIn('id', $mealIds)
                ->get(['id', 'name'])
                ->keyBy('id');

            $meals = collect($mealRows)->map(function ($row) use ($mealsById) {
                $m = $mealsById[$row['meal_id']] ?? null;

                return [
                    'meal_id'     => $row['meal_id'],
                    'name'        => $m?->name,
                    'meal_time'   => $row['meal_time'] ?? null,
                    'day_of_week' => $row['day_of_week'] ?? null,
                ];
            })->values();

            $out[$key] = [
                'price'    => $p['price'] ?? 0,
                'routines' => $routines,
                'meals'    => $meals,
            ];
        }

        return $out;
    }
}
