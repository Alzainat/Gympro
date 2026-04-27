<?php

namespace App\Http\Controllers\Api\Trainer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrainerProfileController extends Controller
{
    public function show(Request $request)
    {
        $profile = $request->user()->profile;

        abort_if(! $profile || $profile->role !== 'trainer', 403);

        $trainer = $profile->trainerProfile()->firstOrCreate([
            'profile_id' => $profile->id,
        ]);

        return response()->json([
            'data' => [
                'full_name' => $profile->full_name,
                'bio' => $profile->bio,
                'avatar_url' => $profile->avatar_url ? Storage::url($profile->avatar_url) : null,
                'hourly_rate' => $trainer->hourly_rate,
                'specializations' => $trainer->specializations ?? [],
                'is_available' => $trainer->is_available,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $profile = $request->user()->profile;

        abort_if(! $profile || $profile->role !== 'trainer', 403);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'specializations' => ['nullable', 'array'],
            'specializations.*' => ['string', 'max:100'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($profile->avatar_url) {
                Storage::disk('public')->delete($profile->avatar_url);
            }

            $data['avatar_url'] = $request->file('avatar')->store('trainer-avatars', 'public');
        }

        $profile->update([
            'full_name' => $data['full_name'],
            'bio' => $data['bio'] ?? null,
            'avatar_url' => $data['avatar_url'] ?? $profile->avatar_url,
        ]);

        $profile->trainerProfile()->updateOrCreate(
            ['profile_id' => $profile->id],
            [
                'hourly_rate' => $data['hourly_rate'] ?? null,
                'experience_years' => $data['experience_years'] ?? null,
                'specializations' => $data['specializations'] ?? [],
                'is_available' => $data['is_available'] ?? true,
            ]
        );

        return response()->json([
            'message' => 'Profile updated successfully',
        ]);
    }
}
