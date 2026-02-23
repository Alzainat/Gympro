<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Models\HealthCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HealthConditionController extends Controller
{
    /**
     * ✅ In-code exercises dataset (always used)
     * Keep it English-friendly so keyword matching works.
     */
    private function defaultExercises(): array
    {
        return [
            ['id' => 1001, 'name' => 'Barbell Squat', 'target_muscle' => 'legs', 'equipment' => 'barbell', 'difficulty' => 'intermediate'],
            ['id' => 1002, 'name' => 'Goblet Squat', 'target_muscle' => 'legs', 'equipment' => 'dumbbell', 'difficulty' => 'beginner'],
            ['id' => 1003, 'name' => 'Leg Press', 'target_muscle' => 'legs', 'equipment' => 'machine', 'difficulty' => 'beginner'],
            ['id' => 1004, 'name' => 'Walking Lunges', 'target_muscle' => 'legs', 'equipment' => 'bodyweight', 'difficulty' => 'beginner'],
            ['id' => 1005, 'name' => 'Deadlift', 'target_muscle' => 'back', 'equipment' => 'barbell', 'difficulty' => 'advanced'],
            ['id' => 1006, 'name' => 'Romanian Deadlift', 'target_muscle' => 'hamstrings', 'equipment' => 'barbell', 'difficulty' => 'intermediate'],
            ['id' => 1007, 'name' => 'Bench Press', 'target_muscle' => 'chest', 'equipment' => 'barbell', 'difficulty' => 'intermediate'],
            ['id' => 1008, 'name' => 'Push Ups', 'target_muscle' => 'chest', 'equipment' => 'bodyweight', 'difficulty' => 'beginner'],
            ['id' => 1009, 'name' => 'Overhead Press', 'target_muscle' => 'shoulders', 'equipment' => 'barbell', 'difficulty' => 'intermediate'],
            ['id' => 1010, 'name' => 'Lateral Raise', 'target_muscle' => 'shoulders', 'equipment' => 'dumbbell', 'difficulty' => 'beginner'],
            ['id' => 1011, 'name' => 'Pull Ups', 'target_muscle' => 'back', 'equipment' => 'bar', 'difficulty' => 'advanced'],
            ['id' => 1012, 'name' => 'Lat Pulldown', 'target_muscle' => 'back', 'equipment' => 'machine', 'difficulty' => 'beginner'],
            ['id' => 1013, 'name' => 'Plank', 'target_muscle' => 'core', 'equipment' => 'bodyweight', 'difficulty' => 'beginner'],
            ['id' => 1014, 'name' => 'Crunches', 'target_muscle' => 'core', 'equipment' => 'bodyweight', 'difficulty' => 'beginner'],
            ['id' => 1015, 'name' => 'Running (Treadmill)', 'target_muscle' => 'cardio', 'equipment' => 'treadmill', 'difficulty' => 'beginner'],
            ['id' => 1016, 'name' => 'Jump Rope', 'target_muscle' => 'cardio', 'equipment' => 'rope', 'difficulty' => 'intermediate'],
        ];
    }

    /**
     * ✅ Default contraindication rules if DB table is empty
     */
    private function defaultRules(): array
    {
        return [
            // knee
            ['condition_keyword' => 'knee', 'target_type' => 'exercise', 'blocked_keyword' => 'squat', 'match_type' => 'partial', 'severity_level' => 'strict',  'reason' => 'High knee load'],
            ['condition_keyword' => 'knee', 'target_type' => 'exercise', 'blocked_keyword' => 'lunge', 'match_type' => 'partial', 'severity_level' => 'warning', 'reason' => 'May increase knee pain'],
            ['condition_keyword' => 'knee', 'target_type' => 'exercise', 'blocked_keyword' => 'leg press', 'match_type' => 'partial', 'severity_level' => 'warning', 'reason' => 'Knee flexion under load'],

            // shoulder
            ['condition_keyword' => 'shoulder', 'target_type' => 'exercise', 'blocked_keyword' => 'overhead press', 'match_type' => 'partial', 'severity_level' => 'warning', 'reason' => 'Overhead position stresses shoulder'],
            ['condition_keyword' => 'shoulder', 'target_type' => 'exercise', 'blocked_keyword' => 'bench',         'match_type' => 'partial', 'severity_level' => 'warning', 'reason' => 'Pressing may irritate shoulder'],
            ['condition_keyword' => 'shoulder', 'target_type' => 'exercise', 'blocked_keyword' => 'pull up',       'match_type' => 'partial', 'severity_level' => 'warning', 'reason' => 'Hanging/pulling may irritate'],

            // back / disc
            ['condition_keyword' => 'back',      'target_type' => 'exercise', 'blocked_keyword' => 'deadlift', 'match_type' => 'partial', 'severity_level' => 'strict',  'reason' => 'High spinal load'],
            ['condition_keyword' => 'disc',      'target_type' => 'exercise', 'blocked_keyword' => 'deadlift', 'match_type' => 'partial', 'severity_level' => 'strict',  'reason' => 'Risk for disc irritation'],
            ['condition_keyword' => 'back pain', 'target_type' => 'exercise', 'blocked_keyword' => 'crunch',   'match_type' => 'partial', 'severity_level' => 'warning', 'reason' => 'Flexion may aggravate back'],

            // ankle
            ['condition_keyword' => 'ankle', 'target_type' => 'exercise', 'blocked_keyword' => 'jump', 'match_type' => 'partial', 'severity_level' => 'warning', 'reason' => 'Impact may worsen ankle pain'],
            ['condition_keyword' => 'ankle', 'target_type' => 'exercise', 'blocked_keyword' => 'run',  'match_type' => 'partial', 'severity_level' => 'warning', 'reason' => 'Impact cardio may irritate'],
        ];
    }

    /**
     * ✅ synonyms for better matching against in-code dataset
     */
    private function aliases(): array
    {
        return [
            'squat' => ['squat','squats','barbell squat','back squat','goblet squat'],
            'lunge' => ['lunge','lunges','walking lunge'],
            'leg press' => ['leg press','legpress'],
            'deadlift' => ['deadlift','dead lift','romanian deadlift'],
            'bench' => ['bench','bench press'],
            'overhead press' => ['overhead press','over head press','shoulder press','military press'],
            'pull up' => ['pull up','pull-up','pullups','chin up'],
            'crunch' => ['crunch','crunches','sit up'],
            'run' => ['run','running','treadmill','jog'],
            'jump' => ['jump','jump rope','skipping','plyo'],
        ];
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
            'user_id' => $profile->id, // FK -> profiles.id
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

    public function check(Request $request)
    {
        $request->validate([
            'conditions' => 'nullable|array',
            'conditions.*' => 'string|max:255',
        ]);

        $profile = $request->user()->profile;

        $conditionNames = $request->input('conditions');

        // If frontend didn't send conditions, take from DB
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

        // ✅ 1) rules: DB first, fallback if empty
        $rules = DB::table('contraindications')
            ->where('target_type', 'exercise')
            ->get();

        $rulesArr = $rules->map(fn($r) => (array) $r)->toArray();
        $rulesSource = 'db';
        if (count($rulesArr) === 0) {
            $rulesArr = $this->defaultRules();
            $rulesSource = 'fallback';
        }

        // ✅ 2) ALWAYS use in-code exercises dataset
        $exercises = $this->defaultExercises();
        $exerciseSource = 'fallback';

        $aliases = $this->aliases();

        $blocked = [];  // strict
        $warnings = []; // warning

        foreach ($rulesArr as $r) {
            $ck = mb_strtolower(trim($r['condition_keyword'] ?? ''));
            if ($ck === '') continue;

            // match condition keyword against provided conditions
            $matched = false;
            foreach ($conditionNames as $cn) {
                $mt = $r['match_type'] ?? 'partial';
                if ($mt === 'exact' && $cn === $ck) { $matched = true; break; }
                if ($mt === 'partial' && str_contains($cn, $ck)) { $matched = true; break; }
            }
            if (!$matched) continue;

            $bk = mb_strtolower(trim($r['blocked_keyword'] ?? ''));
            if ($bk === '') continue;

            $severity = $r['severity_level'] ?? 'strict';

            // ✅ resolve search terms from aliases
            $searchTerms = $aliases[$bk] ?? [$bk];

            // ✅ search ONLY inside in-code dataset
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
                    if ($term === '') continue;

                    if (str_contains($hay, $term)) {
                        $found[$e['id']] = $e; // prevent duplicates
                        break;
                    }
                }
            }

            foreach ($found as $e) {
                $item = [
                    'exercise_id' => $e['id'],
                    'name' => $e['name'],
                    'reason' => $r['reason'] ?? null,
                    'severity_level' => $severity,
                    'matched_condition' => $r['condition_keyword'] ?? null,
                    'matched_keyword' => $r['blocked_keyword'] ?? null,
                ];

                // strict overrides warning
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
                'exercises' => $exerciseSource, // always fallback now
            ],
        ]);
    }
}
