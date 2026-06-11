<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Models\HealthCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HealthConditionController extends Controller
{
    /**
     * Default in-code exercises dataset
     * Used only if exercises table is empty or cannot be fetched.
     */
    private function defaultExercises(): array
    {
        return [
            [
                'id' => 1001,
                'name' => 'Barbell Squat',
                'target_muscle' => 'legs',
                'equipment' => 'barbell',
                'difficulty' => 'intermediate',
                'image' => asset('storage/exercises/barbell.jpg'),
            ],
            [
                'id' => 1002,
                'name' => 'Goblet Squat',
                'target_muscle' => 'legs',
                'equipment' => 'dumbbell',
                'difficulty' => 'beginner',
                'image' => asset('storage/exercises/Goblet.jpg'),
            ],
            [
                'id' => 1003,
                'name' => 'Leg Press',
                'target_muscle' => 'legs',
                'equipment' => 'machine',
                'difficulty' => 'beginner',
                'image' => asset('storage/exercises/Leg Press.jpg'),
            ],
            [
                'id' => 1004,
                'name' => 'Walking Lunges',
                'target_muscle' => 'legs',
                'equipment' => 'bodyweight',
                'difficulty' => 'beginner',
                'image' => asset('storage/exercises/Walking Lunges.jpg'),
            ],
            [
                'id' => 1005,
                'name' => 'Deadlift',
                'target_muscle' => 'back',
                'equipment' => 'barbell',
                'difficulty' => 'advanced',
                'image' => asset('storage/exercises/deadlift.jpg'),
            ],
            [
                'id' => 1006,
                'name' => 'Romanian Deadlift',
                'target_muscle' => 'hamstrings',
                'equipment' => 'barbell',
                'difficulty' => 'intermediate',
                'image' => asset('storage/exercises/romanian-deadlift.jpg'),
            ],
            [
                'id' => 1007,
                'name' => 'Bench Press',
                'target_muscle' => 'chest',
                'equipment' => 'barbell',
                'difficulty' => 'intermediate',
                'image' => asset('storage/exercises/bench-press.jpg'),
            ],
            [
                'id' => 1008,
                'name' => 'Push Ups',
                'target_muscle' => 'chest',
                'equipment' => 'bodyweight',
                'difficulty' => 'beginner',
                'image' => asset('storage/exercises/push-ups.jpg'),
            ],
            [
                'id' => 1009,
                'name' => 'Overhead Press',
                'target_muscle' => 'shoulders',
                'equipment' => 'barbell',
                'difficulty' => 'intermediate',
                'image' => asset('storage/exercises/overhead-press.jpg'),
            ],
            [
                'id' => 1010,
                'name' => 'Lateral Raise',
                'target_muscle' => 'shoulders',
                'equipment' => 'dumbbell',
                'difficulty' => 'beginner',
                'image' => asset('storage/exercises/lateral-raise.jpg'),
            ],
            [
                'id' => 1011,
                'name' => 'Pull Ups',
                'target_muscle' => 'back',
                'equipment' => 'bar',
                'difficulty' => 'advanced',
                'image' => asset('storage/exercises/pull-ups.jpg'),
            ],
            [
                'id' => 1012,
                'name' => 'Lat Pulldown',
                'target_muscle' => 'back',
                'equipment' => 'machine',
                'difficulty' => 'beginner',
                'image' => asset('storage/exercises/lat-pulldown.jpg'),
            ],
            [
                'id' => 1013,
                'name' => 'Plank',
                'target_muscle' => 'core',
                'equipment' => 'bodyweight',
                'difficulty' => 'beginner',
                'image' => asset('storage/exercises/plank.jpg'),
            ],
            [
                'id' => 1014,
                'name' => 'Crunches',
                'target_muscle' => 'core',
                'equipment' => 'bodyweight',
                'difficulty' => 'beginner',
                'image' => asset('storage/exercises/crunches.jpg'),
            ],
            [
                'id' => 1015,
                'name' => 'Running (Treadmill)',
                'target_muscle' => 'cardio',
                'equipment' => 'treadmill',
                'difficulty' => 'beginner',
                'image' => asset('storage/exercises/running-treadmill.jpg'),
            ],
            [
                'id' => 1016,
                'name' => 'Jump Rope',
                'target_muscle' => 'cardio',
                'equipment' => 'rope',
                'difficulty' => 'intermediate',
                'image' => asset('storage/exercises/jump-rope.jpg'),
            ],
        ];
    }

    /**
     * Default in-code meals dataset
     * Used only if meals table is empty or cannot be fetched.
     */
    private function defaultMeals(): array
    {
        return [
            [
                'id' => 2001,
                'name' => 'Milk Oatmeal',
                'description' => 'Oatmeal prepared with milk',
                'ingredients' => ['oats', 'milk', 'banana'],
                'image_url' => asset('storage/meals/milk-oatmeal.jpg'),
            ],
            [
                'id' => 2002,
                'name' => 'Cheese Sandwich',
                'description' => 'Sandwich with cheese',
                'ingredients' => ['bread', 'cheese'],
                'image_url' => asset('storage/meals/cheese-sandwich.jpg'),
            ],
            [
                'id' => 2003,
                'name' => 'Yogurt Bowl',
                'description' => 'Greek yogurt with fruit',
                'ingredients' => ['yogurt', 'berries', 'honey'],
                'image_url' => asset('storage/meals/yogurt-bowl.jpg'),
            ],
            [
                'id' => 2004,
                'name' => 'Chicken Salad',
                'description' => 'Chicken salad without dairy',
                'ingredients' => ['chicken', 'lettuce', 'tomato'],
                'image_url' => asset('storage/meals/chicken-salad.jpg'),
            ],
        ];
    }

    /**
     * Default contraindication rules if DB table is empty or cannot be fetched.
     */
    private function defaultRules(): array
    {
        return [
            ['condition_keyword' => 'knee', 'target_type' => 'exercise', 'blocked_keyword' => 'squat', 'match_type' => 'partial', 'severity_level' => 'strict',  'reason' => 'High knee load'],
            ['condition_keyword' => 'knee', 'target_type' => 'exercise', 'blocked_keyword' => 'lunge', 'match_type' => 'partial', 'severity_level' => 'warning', 'reason' => 'May increase knee pain'],
            ['condition_keyword' => 'knee', 'target_type' => 'exercise', 'blocked_keyword' => 'leg press', 'match_type' => 'partial', 'severity_level' => 'warning', 'reason' => 'Knee flexion under load'],

            ['condition_keyword' => 'shoulder', 'target_type' => 'exercise', 'blocked_keyword' => 'overhead press', 'match_type' => 'partial', 'severity_level' => 'warning', 'reason' => 'Overhead position stresses shoulder'],
            ['condition_keyword' => 'shoulder', 'target_type' => 'exercise', 'blocked_keyword' => 'bench', 'match_type' => 'partial', 'severity_level' => 'warning', 'reason' => 'Pressing may irritate shoulder'],
            ['condition_keyword' => 'shoulder', 'target_type' => 'exercise', 'blocked_keyword' => 'pull up', 'match_type' => 'partial', 'severity_level' => 'warning', 'reason' => 'Hanging/pulling may irritate'],

            ['condition_keyword' => 'back', 'target_type' => 'exercise', 'blocked_keyword' => 'deadlift', 'match_type' => 'partial', 'severity_level' => 'strict', 'reason' => 'High spinal load'],
            ['condition_keyword' => 'disc', 'target_type' => 'exercise', 'blocked_keyword' => 'deadlift', 'match_type' => 'partial', 'severity_level' => 'strict', 'reason' => 'Risk for disc irritation'],
            ['condition_keyword' => 'back pain', 'target_type' => 'exercise', 'blocked_keyword' => 'crunch', 'match_type' => 'partial', 'severity_level' => 'warning', 'reason' => 'Flexion may aggravate back'],

            ['condition_keyword' => 'ankle', 'target_type' => 'exercise', 'blocked_keyword' => 'jump', 'match_type' => 'partial', 'severity_level' => 'warning', 'reason' => 'Impact may worsen ankle pain'],
            ['condition_keyword' => 'ankle', 'target_type' => 'exercise', 'blocked_keyword' => 'run', 'match_type' => 'partial', 'severity_level' => 'warning', 'reason' => 'Impact cardio may irritate'],

            // Meal rules
            ['condition_keyword' => 'lactose', 'target_type' => 'meal', 'blocked_keyword' => 'milk', 'match_type' => 'partial', 'severity_level' => 'warning', 'reason' => 'Contains milk or dairy'],
            ['condition_keyword' => 'lactose', 'target_type' => 'meal', 'blocked_keyword' => 'cheese', 'match_type' => 'partial', 'severity_level' => 'warning', 'reason' => 'Contains dairy'],
            ['condition_keyword' => 'lactose', 'target_type' => 'meal', 'blocked_keyword' => 'yogurt', 'match_type' => 'partial', 'severity_level' => 'warning', 'reason' => 'Contains dairy'],
            ['condition_keyword' => 'milk', 'target_type' => 'meal', 'blocked_keyword' => 'milk', 'match_type' => 'partial', 'severity_level' => 'warning', 'reason' => 'Contains milk'],
        ];
    }

    /**
     * aliases for better matching
     */
    private function aliases(): array
    {
        return [
            'squat' => ['squat', 'squats', 'barbell squat', 'back squat', 'goblet squat'],
            'lunge' => ['lunge', 'lunges', 'walking lunge', 'walking lunges'],
            'leg press' => ['leg press', 'legpress'],
            'deadlift' => ['deadlift', 'dead lift', 'romanian deadlift'],
            'bench' => ['bench', 'bench press'],
            'overhead press' => ['overhead press', 'over head press', 'shoulder press', 'military press'],
            'pull up' => ['pull up', 'pull-up', 'pullups', 'pull ups', 'chin up'],
            'crunch' => ['crunch', 'crunches', 'sit up', 'sit-up'],
            'run' => ['run', 'running', 'treadmill', 'jog', 'jogging'],
            'jump' => ['jump', 'jump rope', 'skipping', 'plyo'],

            // Meal aliases
            'milk' => ['milk', 'dairy', 'cheese', 'yogurt', 'yoghurt', 'cream', 'butter', 'lactose'],
            'cheese' => ['cheese', 'mozzarella', 'cheddar', 'cream cheese'],
            'yogurt' => ['yogurt', 'yoghurt', 'greek yogurt'],
        ];
    }

    public function index(Request $request)
    {
        $profile = $request->user()->profile;

        $conditions = HealthCondition::query()
            ->where('user_id', $profile->id)
            ->latest('detected_at')
            ->get();

        return response()->json([
            'data' => $conditions,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:allergy,injury,condition',
            'name' => 'required|string|max:255',
            'severity' => 'nullable|in:low,medium,high',
            'notes' => 'nullable|string',
        ]);

        $profile = $request->user()->profile;

        $hc = HealthCondition::create([
            'user_id' => $profile->id,
            'type' => $request->type,
            'name' => $request->name,
            'severity' => $request->severity ?? 'medium',
            'notes' => $request->notes,
            'detected_at' => now(),
        ]);

        return response()->json([
            'message' => 'Health condition saved',
            'data' => $hc,
        ], 201);
    }

    public function destroy(Request $request, $id)
    {
        $profile = $request->user()->profile;

        $condition = HealthCondition::query()
            ->where('user_id', $profile->id)
            ->findOrFail($id);

        $condition->delete();

        return response()->json([
            'message' => 'Health condition deleted successfully',
        ]);
    }

    public function check(Request $request)
    {
        $request->validate([
            'conditions' => 'nullable|array',
            'conditions.*' => 'string|max:255',
        ]);

        $profile = $request->user()->profile;

        $conditionNames = $request->input('conditions');

        if (!$conditionNames) {
            $conditionNames = HealthCondition::query()
                ->where('user_id', $profile->id)
                ->pluck('name')
                ->toArray();
        }

        $conditionNames = collect($conditionNames)
            ->map(fn($v) => mb_strtolower(trim($v)))
            ->filter()
            ->values()
            ->all();

        /*
         * Fetch contraindication rules from database.
         * If table is empty or fetch fails, use defaultRules().
         */
        $rulesSource = 'db';

        try {
            $rulesRows = DB::table('contraindications')->get();

            if ($rulesRows->isEmpty()) {
                $rulesArr = $this->defaultRules();
                $rulesSource = 'fallback';
            } else {
                $rulesArr = $rulesRows
                    ->map(fn($r) => [
                        'condition_keyword' => mb_strtolower(trim($r->condition_keyword ?? '')),
                        'target_type' => mb_strtolower(trim($r->target_type ?? '')),
                        'blocked_keyword' => mb_strtolower(trim($r->blocked_keyword ?? '')),
                        'match_type' => mb_strtolower(trim($r->match_type ?? 'partial')),
                        'severity_level' => mb_strtolower(trim($r->severity_level ?? 'strict')),
                        'reason' => $r->reason ?? null,
                    ])
                    ->toArray();
            }
        } catch (\Throwable $e) {
            $rulesArr = $this->defaultRules();
            $rulesSource = 'fallback';
        }

        /*
         * Fetch exercises from database.
         * If exercises table is empty or fetch fails, use defaultExercises().
         */
        $exerciseSource = 'db';

        try {
            $exerciseRows = DB::table('exercises')->get();

            if ($exerciseRows->isEmpty()) {
                $exercises = $this->defaultExercises();
                $exerciseSource = 'fallback';
            } else {
                $exercises = $exerciseRows
                    ->map(fn($e) => [
                        'id' => $e->id,
                        'name' => $e->name,
                        'target_muscle' => $e->target_muscle ?? '',
                        'equipment' => $e->equipment ?? '',
                        'difficulty' => $e->difficulty ?? '',
                        'image' => $e->image_url ?? $e->image ?? null,
                    ])
                    ->toArray();
            }
        } catch (\Throwable $e) {
            $exercises = $this->defaultExercises();
            $exerciseSource = 'fallback';
        }

        /*
         * Fetch meals from database.
         * If meals table is empty or fetch fails, use defaultMeals().
         */
        $mealSource = 'db';

        try {
            $mealRows = DB::table('meals')->get();

            if ($mealRows->isEmpty()) {
                $meals = $this->defaultMeals();
                $mealSource = 'fallback';
            } else {
                $meals = $mealRows
                    ->map(fn($m) => [
                        'id' => $m->id,
                        'name' => $m->name,
                        'description' => $m->description ?? '',
                        'ingredients' => $m->ingredients ?? '[]',
                        'image_url' => $m->image_url ?? $m->image ?? null,
                    ])
                    ->toArray();
            }
        } catch (\Throwable $e) {
            $meals = $this->defaultMeals();
            $mealSource = 'fallback';
        }

        $aliases = $this->aliases();

        $blocked = [];
        $warnings = [];

        $blockedMeals = [];
        $mealWarnings = [];

        /*
         * Check exercise rules
         */
        foreach ($rulesArr as $r) {
            $targetType = mb_strtolower(trim($r['target_type'] ?? ''));

            if ($targetType !== 'exercise') {
                continue;
            }

            $ck = mb_strtolower(trim($r['condition_keyword'] ?? ''));

            if ($ck === '') {
                continue;
            }

            $matched = false;

            foreach ($conditionNames as $cn) {
                $mt = mb_strtolower(trim($r['match_type'] ?? 'partial'));

                if ($mt === 'exact' && $cn === $ck) {
                    $matched = true;
                    break;
                }

                if ($mt === 'partial' && str_contains($cn, $ck)) {
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                continue;
            }

            $bk = mb_strtolower(trim($r['blocked_keyword'] ?? ''));

            if ($bk === '') {
                continue;
            }

            $severity = mb_strtolower(trim($r['severity_level'] ?? 'strict'));
            $searchTerms = $aliases[$bk] ?? [$bk];

            $found = [];

            foreach ($exercises as $e) {
                $hay = mb_strtolower(
                    ($e['name'] ?? '') . ' ' .
                    ($e['target_muscle'] ?? '') . ' ' .
                    ($e['equipment'] ?? '') . ' ' .
                    ($e['difficulty'] ?? '')
                );

                foreach ($searchTerms as $term) {
                    $term = mb_strtolower(trim($term));

                    if ($term === '') {
                        continue;
                    }

                    if (str_contains($hay, $term)) {
                        $found[$e['id']] = $e;
                        break;
                    }
                }
            }

            foreach ($found as $e) {
                $item = [
                    'exercise_id' => $e['id'],
                    'name' => $e['name'],
                    'image' => $e['image'] ?? null,
                    'reason' => $r['reason'] ?? null,
                    'severity_level' => $severity,
                    'matched_condition' => $r['condition_keyword'] ?? null,
                    'matched_keyword' => $r['blocked_keyword'] ?? null,
                ];

                if ($severity === 'strict') {
                    $blocked[$e['id']] = $item;
                    unset($warnings[$e['id']]);
                } else {
                    if (!isset($blocked[$e['id']])) {
                        $warnings[$e['id']] = $item;
                    }
                }
            }
        }

        /*
         * Check meal rules
         */
        foreach ($rulesArr as $r) {
            $targetType = mb_strtolower(trim($r['target_type'] ?? ''));

            if ($targetType !== 'meal') {
                continue;
            }

            $ck = mb_strtolower(trim($r['condition_keyword'] ?? ''));

            if ($ck === '') {
                continue;
            }

            $matched = false;

            foreach ($conditionNames as $cn) {
                $mt = mb_strtolower(trim($r['match_type'] ?? 'partial'));

                if ($mt === 'exact' && $cn === $ck) {
                    $matched = true;
                    break;
                }

                if ($mt === 'partial' && str_contains($cn, $ck)) {
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                continue;
            }

            $bk = mb_strtolower(trim($r['blocked_keyword'] ?? ''));

            if ($bk === '') {
                continue;
            }

            $severity = mb_strtolower(trim($r['severity_level'] ?? 'strict'));
            $searchTerms = $aliases[$bk] ?? [$bk];

            foreach ($meals as $meal) {
                $ingredients = $meal['ingredients'] ?? [];

                if (is_string($ingredients)) {
                    $decoded = json_decode($ingredients, true);

                    if (is_array($decoded)) {
                        $ingredients = $decoded;
                    } else {
                        $ingredients = [$ingredients];
                    }
                }

                if (!is_array($ingredients)) {
                    $ingredients = [];
                }

                $hay = mb_strtolower(
                    ($meal['name'] ?? '') . ' ' .
                    ($meal['description'] ?? '') . ' ' .
                    implode(' ', $ingredients)
                );

                $mealMatched = false;

                foreach ($searchTerms as $term) {
                    $term = mb_strtolower(trim($term));

                    if ($term === '') {
                        continue;
                    }

                    if (str_contains($hay, $term)) {
                        $mealMatched = true;
                        break;
                    }
                }

                if (!$mealMatched) {
                    continue;
                }

                $item = [
                    'meal_id' => $meal['id'],
                    'name' => $meal['name'],
                    'image' => $meal['image_url'] ?? $meal['image'] ?? null,
                    'reason' => $r['reason'] ?? null,
                    'severity_level' => $severity,
                    'matched_condition' => $r['condition_keyword'] ?? null,
                    'matched_keyword' => $r['blocked_keyword'] ?? null,
                ];

                if ($severity === 'strict') {
                    $blockedMeals[$meal['id']] = $item;
                    unset($mealWarnings[$meal['id']]);
                } else {
                    if (!isset($blockedMeals[$meal['id']])) {
                        $mealWarnings[$meal['id']] = $item;
                    }
                }
            }
        }

        return response()->json([
            'conditions_used' => $conditionNames,

            'blocked_exercises' => array_values($blocked),
            'warnings' => array_values($warnings),

            'blocked_meals' => array_values($blockedMeals),
            'meal_warnings' => array_values($mealWarnings),

            'data_source' => [
                'rules' => $rulesSource,
                'exercises' => $exerciseSource,
                'meals' => $mealSource,
            ],
        ]);
    }
}
