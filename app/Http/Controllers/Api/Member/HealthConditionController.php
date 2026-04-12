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
     * Default contraindication rules if DB table is empty
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
        ];
    }

    /**
     * aliases for better matching
     */
    private function aliases(): array
    {
        return [
            'squat' => ['squat', 'squats', 'barbell squat', 'back squat', 'goblet squat'],
            'lunge' => ['lunge', 'lunges', 'walking lunge'],
            'leg press' => ['leg press', 'legpress'],
            'deadlift' => ['deadlift', 'dead lift', 'romanian deadlift'],
            'bench' => ['bench', 'bench press'],
            'overhead press' => ['overhead press', 'over head press', 'shoulder press', 'military press'],
            'pull up' => ['pull up', 'pull-up', 'pullups', 'chin up'],
            'crunch' => ['crunch', 'crunches', 'sit up'],
            'run' => ['run', 'running', 'treadmill', 'jog'],
            'jump' => ['jump', 'jump rope', 'skipping', 'plyo'],
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

        $rules = DB::table('contraindications')
            ->where('target_type', 'exercise')
            ->get();

        $rulesArr = $rules->map(fn($r) => (array) $r)->toArray();
        $rulesSource = 'db';

        if (count($rulesArr) === 0) {
            $rulesArr = $this->defaultRules();
            $rulesSource = 'fallback';
        }

        $exercises = $this->defaultExercises();
        $exerciseSource = 'fallback';

        $aliases = $this->aliases();

        $blocked = [];
        $warnings = [];

        foreach ($rulesArr as $r) {
            $ck = mb_strtolower(trim($r['condition_keyword'] ?? ''));
            if ($ck === '') {
                continue;
            }

            $matched = false;
            foreach ($conditionNames as $cn) {
                $mt = $r['match_type'] ?? 'partial';

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

            $severity = $r['severity_level'] ?? 'strict';
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

        return response()->json([
            'conditions_used' => $conditionNames,
            'blocked_exercises' => array_values($blocked),
            'warnings' => array_values($warnings),
            'data_source' => [
                'rules' => $rulesSource,
                'exercises' => $exerciseSource,
            ],
        ]);
    }
}
