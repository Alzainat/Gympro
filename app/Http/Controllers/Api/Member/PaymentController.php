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
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

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
     * Body:
     * {
     *   goal: cutting|bulking,
     *   plan_key: bronze|silver|gold,
     *   payment_method: cash|credit_card|debit_card,
     *   card_holder_name: required for card payment,
     *   card_number: required for card payment,
     *   expiry_date: required for card payment, format MM/YY,
     *   cvc: required for card payment
     * }
     */
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'goal' => 'required|in:cutting,bulking',
            'plan_key' => 'required|in:bronze,silver,gold',
            'payment_method' => 'required|in:cash,credit_card,debit_card,bank_transfer,digital_wallet',

            // Required only when payment method is credit_card or debit_card
            'card_holder_name' => [
                'required_if:payment_method,credit_card,debit_card',
                'nullable',
                'string',
                'max:100',
                'regex:/^[A-Z]+(?: [A-Z]+)*$/',
            ],

            'card_number' => [
                'required_if:payment_method,credit_card,debit_card',
                'nullable',
                'digits:14',
            ],

            'expiry_date' => [
                'required_if:payment_method,credit_card,debit_card',
                'nullable',
                'regex:/^(0[1-9]|1[0-2])\/\d{2}$/',
            ],

            'cvc' => [
                'required_if:payment_method,credit_card,debit_card',
                'nullable',
                'digits:3',
            ],
        ], [
            'goal.required' => 'Please select a goal first.',
            'goal.in' => 'Invalid goal selected.',

            'plan_key.required' => 'Please select a plan first.',
            'plan_key.in' => 'Invalid plan selected.',

            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Invalid payment method.',

            'card_holder_name.required_if' => 'Name on card is required.',
            'card_holder_name.regex' => 'Name on card must be CAPITAL LETTERS only, exactly like the card.',
            'card_holder_name.max' => 'Name on card is too long.',

            'card_number.required_if' => 'Card number is required.',
            'card_number.digits' => 'Card number must be exactly 14 digits.',

            'expiry_date.required_if' => 'Expiry date is required.',
            'expiry_date.regex' => 'Expiry date must be in MM/YY format.',

            'cvc.required_if' => 'CVC is required.',
            'cvc.digits' => 'CVC must be exactly 3 digits.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $paymentMethod = $request->input('payment_method');

            if (!in_array($paymentMethod, ['credit_card', 'debit_card'])) {
                return;
            }

            $expiryDate = $request->input('expiry_date');

            if (!$expiryDate || !preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiryDate)) {
                return;
            }

            [$month, $year] = explode('/', $expiryDate);

            $month = (int) $month;
            $year = 2000 + (int) $year;

            $cardExpiry = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            $today = now()->startOfDay();

            if ($cardExpiry->lt($today)) {
                $validator->errors()->add('expiry_date', 'Card is expired.');
            }
        });

        $data = $validator->validate();

        $profile = $request->user()->profile;

        if (!$profile) {
            return response()->json([
                'message' => 'Profile not found.',
            ], 404);
        }

        $profileId = $profile->id;

        $plan = config('plans.' . $data['goal'] . '.' . $data['plan_key']);

        if (!$plan || !is_array($plan)) {
            return response()->json([
                'message' => 'Selected plan is missing in configuration. Payment aborted.',
            ], 422);
        }

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        return DB::transaction(function () use ($data, $profile, $profileId, $plan, $days) {
            $startDate = now()->toDateString();
            $durationDays = (int) ($plan['duration_days'] ?? 30);
            $endDate = Carbon::parse($startDate)->addDays($durationDays - 1)->toDateString();

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

            if ($profile->memberProfile) {
                $profile->memberProfile->update([
                    'membership_tier' => $data['plan_key'],
                ]);
            }

            MemberRoutine::where('member_id', $profileId)
                ->where('source', 'payment')
                ->update([
                    'status' => 'archived',
                ]);

            MemberMeal::where('member_id', $profileId)
                ->where('source', 'payment')
                ->update([
                    'is_active' => 0,
                ]);

            foreach ($routineIds as $rid) {
                MemberRoutine::create([
                    'member_id' => $profileId,
                    'routine_id' => $rid,
                    'assigned_by' => null,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'source' => 'payment',
                    'status' => 'active',
                ]);
            }

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
                    'grams' => $row['grams'] ?? null,
                    'day_of_week' => $assignedDay,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'source' => 'payment',
                    'is_active' => 1,
                ]);
            }

            /*
             * Important:
             * Do not store full card number or CVC in database.
             * This is only a demo checkout validation.
             */
            $payment = Payment::create([
                'user_id' => $profileId,
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

        $isMultiGoal = !$goal;

        if ($isMultiGoal) {
            foreach ($plans as $g => $tiers) {
                $result[$g] = $this->buildGoalPlanDetails($tiers);
            }

            return response()->json($result);
        }

        $result = $this->buildGoalPlanDetails($plans);

        return response()->json($result);
    }

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
                    'grams' => $row['grams'] ?? null,
                ];
            })->values();

            $out[$key] = [
                'price' => $p['price'] ?? 0,
                'duration_days' => $p['duration_days'] ?? 30,
                'routines' => $routines,
                'meals' => $meals,
            ];
        }

        return $out;
    }
}
