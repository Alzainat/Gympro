<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Models\WorkoutRoutine;
use Illuminate\Http\Request;

class RoutineController extends Controller
{
    public function index()
    {
        return response()->json(
            WorkoutRoutine::query()
                ->where('is_public', 1)
                ->get()
        );
    }

    public function show($id)
    {
        $routine = WorkoutRoutine::query()
            ->with('exercises.exercise')
            ->findOrFail($id);

        return response()->json($routine);
    }

    public function assignToMe(Request $request, $routineId)
    {
        $profile = $request->user()->profile;

        if (!$profile) {
            return response()->json([
                'message' => 'Profile not found.',
            ], 404);
        }

        $routine = WorkoutRoutine::query()->findOrFail($routineId);

        $startDate = now()->toDateString();
        $endDate = now()->addMonth()->toDateString();

        $alreadyAssigned = $profile->memberRoutines()
            ->where('routine_id', $routineId)
            ->where('status', 'active')
            ->whereDate('end_date', '>=', now()->toDateString())
            ->exists();

        if ($alreadyAssigned) {
            return response()->json([
                'message' => 'Routine already assigned and still active.',
            ], 422);
        }

        $profile->memberRoutines()->create([
            'routine_id' => $routine->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'Routine assigned successfully.',
            'start_date' => $startDate,
            'end_date' => $endDate,
        ], 201);
    }
}
