<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Models\MemberRoutine;
use App\Models\WorkoutRoutine;
use Illuminate\Http\Request;

class WorkoutController extends Controller
{
    /**
     * ✅ جدول التمارين للعضو (Grouped by Routine)
     * GET /member/workouts
     *
     * Output:
     * [
     *   {
     *     "routine_id": 1,
     *     "routine_name": "...",
     *     "exercises": [
     *        { exercise... },
     *        ...
     *     ]
     *   },
     *   ...
     * ]
     */
    public function index(Request $request)
{
    $profileId = $request->user()->profile->id;

    $routineIds = MemberRoutine::query()
        ->where('member_id', $profileId)
        ->where('status', 'active')
        ->pluck('routine_id')
        ->unique()
        ->values();

    if ($routineIds->isEmpty()) {
        return response()->json((object)[]);
    }

    $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

    $routines = WorkoutRoutine::query()
        ->whereIn('id', $routineIds)
        ->with([
            'routineExercises' => function ($q) {
                $q->orderBy('day_of_week')->orderBy('order_index');
            },
            'routineExercises.exercise:id,name,target_muscle,equipment,difficulty,video_url,image_url'
        ])
        ->get();

    // ✅ build per-day groups
    $result = [];
    foreach ($days as $d) $result[$d] = [];

    foreach ($routines as $routine) {
        // group this routine's exercises by day
        $byDay = $routine->routineExercises
            ->filter(fn($re) => $re->exercise)
            ->groupBy(fn($re) => $re->day_of_week ?? 'Monday');

        foreach ($days as $day) {
            $list = $byDay->get($day, collect());

            if ($list->isEmpty()) continue;

            $exercises = $list->map(function ($re) {
                $ex = $re->exercise;

                return [
                    'day_of_week'   => $re->day_of_week, // ✅ مهم
                    'exercise_id'   => $ex->id,
                    'exercise_name' => $ex->name,
                    'target_muscle' => $ex->target_muscle,
                    'equipment'     => $ex->equipment,
                    'difficulty'    => $ex->difficulty,
                    'video_url'     => $ex->video_url,
                    'image_url'     => $ex->image_url,

                    'sets'         => $re->sets,
                    'reps'         => $re->reps,
                    'rest_seconds' => $re->rest_seconds,
                    'order_index'  => $re->order_index,
                    'notes'        => $re->notes,
                ];
            })->values();

            $result[$day][] = [
                'routine_id'   => $routine->id,
                'routine_name' => $routine->name,
                'description'  => $routine->description,
                'difficulty'   => $routine->difficulty_level,
                'exercises'    => $exercises,
            ];
        }
    }

    return response()->json($result);
}
}
