<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Models\WorkoutRoutine;
use Illuminate\Http\Request;

class RoutineController extends Controller
{
    public function index()
    {
        return WorkoutRoutine::where('is_public', 1)->get();
    }

    public function show($id)
    {
        return WorkoutRoutine::with('exercises.exercise')
            ->findOrFail($id);
    }

    public function assignToMe(Request $request, $routineId)
    {
        $request->user()->profile->memberRoutines()->create([
            'routine_id' => $routineId
        ]);

        return response()->json(['message' => 'Routine assigned']);
    }
}