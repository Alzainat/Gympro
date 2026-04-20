<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Models\MemberRoutine;
use App\Models\WorkoutRoutine;
use App\Models\RoutineExercise;
use App\Models\MemberExerciseLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WorkoutController extends Controller
{
    /**
     * جدول التمارين للعضو + log اليوم المحدد
     * GET /member/workouts?date=2026-04-09
     */
    public function index(Request $request)
    {
        $profile = $request->user()->profile;

        if (!$profile) {
            return response()->json([
                'message' => 'Profile not found.',
            ], 404);
        }

        $profileId = $profile->id;

        $selectedDate = $request->query('date')
            ? Carbon::parse($request->query('date'))->toDateString()
            : now()->toDateString();

        $memberRoutines = MemberRoutine::query()
            ->where('member_id', $profileId)
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $selectedDate)
            ->where(function ($q) use ($selectedDate) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $selectedDate);
            })
            ->get();

        $routineIds = $memberRoutines
            ->pluck('routine_id')
            ->unique()
            ->values();

        if ($routineIds->isEmpty()) {
            return response()->json([
                'meta' => [
                    'selected_date' => $selectedDate,
                    'plan_timers' => [],
                ],
                'days' => (object) [],
            ]);
        }

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        $routines = WorkoutRoutine::query()
            ->whereIn('id', $routineIds)
            ->with([
                'routineExercises' => function ($q) {
                    $q->orderBy('day_of_week')->orderBy('order_index');
                },
                'routineExercises.exercise:id,name,target_muscle,equipment,difficulty,video_url,image_url',
            ])
            ->get()
            ->keyBy('id');

        $logs = MemberExerciseLog::query()
            ->with('sets')
            ->where('member_id', $profileId)
            ->whereDate('workout_date', $selectedDate)
            ->get()
            ->keyBy('routine_exercise_id');

        $result = [];
        foreach ($days as $d) {
            $result[$d] = [];
        }

        foreach ($memberRoutines as $memberRoutine) {
            $routine = $routines->get($memberRoutine->routine_id);

            if (!$routine) {
                continue;
            }

            $byDay = $routine->routineExercises
                ->filter(fn ($re) => $re->exercise)
                ->groupBy(fn ($re) => $re->day_of_week ?? 'Monday');

            foreach ($days as $day) {
                $list = $byDay->get($day, collect());

                if ($list->isEmpty()) {
                    continue;
                }

                $exercises = $list->map(function ($re) use ($logs) {
                    $ex = $re->exercise;
                    $log = $logs->get($re->id);

                    return [
                        'routine_exercise_id' => $re->id,
                        'day_of_week' => $re->day_of_week,
                        'exercise_id' => $ex->id,
                        'exercise_name' => $ex->name,
                        'target_muscle' => $ex->target_muscle,
                        'equipment' => $ex->equipment,
                        'difficulty' => $ex->difficulty,
                        'video_url' => $ex->video_url,
                        'image_url' => $ex->image_url,
                        'sets' => $re->sets,
                        'reps' => $re->reps,
                        'rest_seconds' => $re->rest_seconds,
                        'order_index' => $re->order_index,
                        'notes' => $re->notes,
                        'member_log' => $log ? [
                            'id' => $log->id,
                            'workout_date' => optional($log->workout_date)->toDateString(),
                            'note' => $log->note,
                            'sets' => $log->sets->map(function ($set) {
                                return [
                                    'id' => $set->id,
                                    'round' => $set->round,
                                    'weight' => $set->weight,
                                    'reps' => $set->reps,
                                    'done' => (bool) $set->done,
                                ];
                            })->values(),
                        ] : null,
                    ];
                })->values();

                $result[$day][] = [
                    'member_routine_id' => $memberRoutine->id,
                    'routine_id' => $routine->id,
                    'routine_name' => $routine->name,
                    'description' => $routine->description,
                    'difficulty' => $routine->difficulty_level,
                    'selected_date' => $selectedDate,
                    'start_date' => optional($memberRoutine->start_date)->toDateString(),
                    'end_date' => optional($memberRoutine->end_date)->toDateString(),
                    'status' => $memberRoutine->status,
                    'source' => $memberRoutine->source,
                    'assigned_by' => $memberRoutine->assigned_by,
                    'exercises' => $exercises,
                ];
            }
        }

        $planTimers = $memberRoutines
            ->groupBy('source')
            ->map(function ($items, $source) {
                $first = $items->sortBy('start_date')->first();
                $last = $items->filter(fn ($x) => !empty($x->end_date))->sortByDesc('end_date')->first();

                return [
                    'id' => $source . '-plan',
                    'title' => $source === 'trainer' ? 'Trainer Plan' : 'Website Plan',
                    'source' => $source,
                    'start_date' => optional($first?->start_date)->toDateString(),
                    'end_date' => optional($last?->end_date)->toDateString(),
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'meta' => [
                'selected_date' => $selectedDate,
                'plan_timers' => $planTimers,
            ],
            'days' => $result,
        ]);
    }

    /**
     * حفظ log للعضو على تمرين محدد في تاريخ محدد
     * POST /member/workouts/log
     */
    public function saveExerciseLog(Request $request)
    {
        $profile = $request->user()->profile;

        if (!$profile) {
            return response()->json([
                'message' => 'Profile not found.',
            ], 404);
        }

        $profileId = $profile->id;

        $data = $request->validate([
            'routine_id' => ['required', 'integer', 'exists:workout_routines,id'],
            'routine_exercise_id' => ['required', 'integer', 'exists:routine_exercises,id'],
            'exercise_id' => ['required', 'integer', 'exists:exercises,id'],
            'workout_date' => ['required', 'date'],
            'day_of_week' => [
                'required',
                Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']),
            ],
            'note' => ['nullable', 'string', 'max:2000'],

            'sets' => ['required', 'array', 'min:1'],
            'sets.*.round' => ['required', 'integer', 'min:1'],
            'sets.*.weight' => ['nullable', 'numeric', 'min:0'],
            'sets.*.reps' => ['nullable', 'integer', 'min:0'],
            'sets.*.done' => ['nullable', 'boolean'],
        ]);

        $routineExercise = RoutineExercise::query()
            ->where('id', $data['routine_exercise_id'])
            ->where('routine_id', $data['routine_id'])
            ->where('exercise_id', $data['exercise_id'])
            ->first();

        if (!$routineExercise) {
            return response()->json([
                'message' => 'Invalid routine exercise data.',
            ], 422);
        }

        $memberHasRoutine = MemberRoutine::query()
            ->where('member_id', $profileId)
            ->where('routine_id', $data['routine_id'])
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $data['workout_date'])
            ->where(function ($q) use ($data) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $data['workout_date']);
            })
            ->exists();

        if (!$memberHasRoutine) {
            return response()->json([
                'message' => 'You are not allowed to log this routine.',
            ], 403);
        }

        $log = DB::transaction(function () use ($profileId, $data) {
            $log = MemberExerciseLog::updateOrCreate(
                [
                    'member_id' => $profileId,
                    'routine_exercise_id' => $data['routine_exercise_id'],
                    'workout_date' => Carbon::parse($data['workout_date'])->toDateString(),
                ],
                [
                    'routine_id' => $data['routine_id'],
                    'exercise_id' => $data['exercise_id'],
                    'day_of_week' => $data['day_of_week'],
                    'note' => $data['note'] ?? null,
                ]
            );

            $log->sets()->delete();

            foreach ($data['sets'] as $set) {
                $log->sets()->create([
                    'round' => $set['round'],
                    'weight' => $set['weight'] ?? null,
                    'reps' => $set['reps'] ?? null,
                    'done' => (bool) ($set['done'] ?? false),
                ]);
            }

            return $log->load('sets');
        });

        return response()->json([
            'message' => 'Workout log saved successfully.',
            'data' => [
                'id' => $log->id,
                'member_id' => $log->member_id,
                'routine_id' => $log->routine_id,
                'routine_exercise_id' => $log->routine_exercise_id,
                'exercise_id' => $log->exercise_id,
                'workout_date' => optional($log->workout_date)->toDateString(),
                'day_of_week' => $log->day_of_week,
                'note' => $log->note,
                'sets' => $log->sets->map(function ($set) {
                    return [
                        'id' => $set->id,
                        'round' => $set->round,
                        'weight' => $set->weight,
                        'reps' => $set->reps,
                        'done' => (bool) $set->done,
                    ];
                })->values(),
            ],
        ]);
    }

    public function show($id)
    {
        $routine = WorkoutRoutine::query()
            ->with([
                'routineExercises.exercise:id,name,target_muscle,equipment,difficulty,video_url,image_url',
            ])
            ->findOrFail($id);

        return response()->json($routine);
    }

    public function assign(Request $request, $id)
    {
        return response()->json([
            'message' => 'Assign endpoint is currently not implemented here.',
        ], 501);
    }
}
