<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;

class MemberTrainerSubscriptionController extends Controller
{
    public function store(Request $request, Profile $trainer)
    {
        $member = $request->user()->profile;

        if (!$member) {
            return response()->json([
                'message' => 'Profile not found.'
            ], 404);
        }

        if ($member->role !== 'member') {
            return response()->json([
                'message' => 'Only members can subscribe to trainers.'
            ], 403);
        }

        if ($trainer->role !== 'trainer') {
            return response()->json([
                'message' => 'Selected profile is not a trainer.'
            ], 422);
        }

        $member->trainer_id = $trainer->id;
        $member->save();
        $member->load('trainer');

        return response()->json([
            'message' => 'Trainer subscribed successfully.',
            'trainer_id' => $member->trainer_id,
            'trainer' => $member->trainer,
        ]);
    }

    public function destroy(Request $request)
    {
        $member = $request->user()->profile;

        if (!$member) {
            return response()->json([
                'message' => 'Profile not found.'
            ], 404);
        }

        if ($member->role !== 'member') {
            return response()->json([
                'message' => 'Only members can unsubscribe from trainers.'
            ], 403);
        }

        $member->trainer_id = null;
        $member->save();

        return response()->json([
            'message' => 'Trainer subscription removed successfully.',
            'trainer_id' => null,
        ]);
    }
}
