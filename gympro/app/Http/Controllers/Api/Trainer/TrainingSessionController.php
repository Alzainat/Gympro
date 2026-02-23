<?php

namespace App\Http\Controllers\Api\Trainer;

use App\Http\Controllers\Controller;
use App\Models\TrainingSession;
use Illuminate\Http\Request;
use App\Services\TrainerNotifier;
use App\Models\Profile;

class TrainingSessionController extends Controller
{
    public function index(Request $request)
    {
        return TrainingSession::where(
            'trainer_id',
            $request->user()->profile->id
        )->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'session_date' => 'required|date',
            'duration_minutes' => 'required|integer',
            'max_participants' => 'required|integer'
        ]);

        return TrainingSession::create([
            'trainer_id' => $request->user()->profile->id,
            ...$data
        ]);
    }

    public function toggle($id)
    {
        $session = TrainingSession::findOrFail($id);
        $session->update([
            'is_active' => !$session->is_active
        ]);

        return response()->json(['message' => 'Session status updated']);
    }


}