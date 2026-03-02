<?php

namespace App\Http\Controllers\Api\Trainer;

use App\Http\Controllers\Controller;
use App\Models\TrainerAvailability;
use Illuminate\Http\Request;

class TrainerScheduleController extends Controller
{
    public function index(Request $request)
    {
        return TrainerAvailability::where(
            'trainer_id',
            $request->user()->profile->id
        )->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'day_of_week' => 'required',
            'start_time' => 'required',
            'end_time' => 'required'
        ]);

        return TrainerAvailability::create([
            'trainer_id' => $request->user()->profile->id,
            ...$data
        ]);
    }

    public function toggle($id)
    {
        $schedule = TrainerAvailability::findOrFail($id);
        $schedule->update([
            'is_available' => !$schedule->is_available
        ]);

        return response()->json(['message' => 'Availability updated']);
    }
}