<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthProfile;
use Illuminate\Http\Request;

class MemberHealthProfileController extends Controller
{
    public function show(Request $request)
    {
        $profileId = $request->user()?->profile?->id;

        if (!$profileId) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        $hp = HealthProfile::where('user_id', $profileId)->first();

        return response()->json([
            'exists' => (bool) $hp,
            'data' => $hp ? [
                'height' => $hp->height,
                'weight' => $hp->weight,
                'body_fat_percentage' => $hp->body_fat_percentage,
            ] : null,
        ]);
    }

    public function upsert(Request $request)
    {
        $profileId = $request->user()?->profile?->id;

        if (!$profileId) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        $validated = $request->validate([
            'height' => ['required', 'numeric', 'min:50', 'max:260'],
            'weight' => ['required', 'numeric', 'min:20', 'max:500'],
            'body_fat_percentage' => ['nullable', 'numeric', 'min:1', 'max:80'],
        ]);

        $hp = HealthProfile::updateOrCreate(
            ['user_id' => $profileId],
            [
                'height' => $validated['height'],
                'weight' => $validated['weight'],
                'body_fat_percentage' => $validated['body_fat_percentage'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Saved',
            'data' => [
                'height' => $hp->height,
                'weight' => $hp->weight,
                'body_fat_percentage' => $hp->body_fat_percentage,
            ],
        ]);
    }
}
