<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Models\MemberProgressPhoto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProgressPhotoController extends Controller
{
    public function index(Request $request)
    {
        $profile = $request->user()->profile;

        $query = MemberProgressPhoto::query()
            ->where('member_id', $profile->id)
            ->orderByDesc('taken_at')
            ->orderByDesc('id');

        if ($request->filled('pose')) {
            $query->where('pose', $request->pose);
        }

        $photos = $query->get()->map(function ($photo) {
            return [
                'id' => $photo->id,
                'member_id' => $photo->member_id,
                'photo_type' => $photo->photo_type,
                'pose' => $photo->pose,
                'notes' => $photo->notes,
                'taken_at' => optional($photo->taken_at)->format('Y-m-d'),
                'image_path' => $photo->image_path,
                'image_url' => asset('storage/' . $photo->image_path),
                'created_at' => optional($photo->created_at)->toDateTimeString(),
                'updated_at' => optional($photo->updated_at)->toDateTimeString(),
            ];
        });

        return response()->json($photos);
    }

    public function store(Request $request)
    {
        $profile = $request->user()->profile;

        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'photo_type' => ['nullable', Rule::in(['baseline', 'progress', 'comparison'])],
            'pose' => ['nullable', Rule::in(['front', 'side', 'back'])],
            'notes' => ['nullable', 'string', 'max:1000'],
            'taken_at' => ['nullable', 'date'],
        ]);

        $path = $request->file('image')->store('member-progress-photos', 'public');

        $photo = MemberProgressPhoto::create([
            'member_id' => $profile->id,
            'image_path' => $path,
            'photo_type' => $validated['photo_type'] ?? 'progress',
            'pose' => $validated['pose'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'taken_at' => $validated['taken_at'] ?? now()->toDateString(),
        ]);

        return response()->json([
            'message' => 'Progress photo uploaded successfully.',
            'photo' => [
                'id' => $photo->id,
                'member_id' => $photo->member_id,
                'photo_type' => $photo->photo_type,
                'pose' => $photo->pose,
                'notes' => $photo->notes,
                'taken_at' => optional($photo->taken_at)->format('Y-m-d'),
                'image_path' => $photo->image_path,
                'image_url' => asset('storage/' . $photo->image_path),
                'created_at' => optional($photo->created_at)->toDateTimeString(),
            ]
        ], 201);
    }

    public function destroy(Request $request, $id)
    {
        $profile = $request->user()->profile;

        $photo = MemberProgressPhoto::where('member_id', $profile->id)->findOrFail($id);

        if ($photo->image_path && Storage::disk('public')->exists($photo->image_path)) {
            Storage::disk('public')->delete($photo->image_path);
        }

        $photo->delete();

        return response()->json([
            'message' => 'Progress photo deleted successfully.'
        ]);
    }

    public function comparison(Request $request)
    {
        $profile = $request->user()->profile;

        $validated = $request->validate([
            'months' => ['nullable', 'integer', 'min:1', 'max:24'],
            'pose' => ['nullable', Rule::in(['front', 'side', 'back'])],
        ]);

        $months = (int) ($validated['months'] ?? 3);
        $pose = $validated['pose'] ?? null;

        $baselineQuery = MemberProgressPhoto::query()
            ->where('member_id', $profile->id);

        if ($pose) {
            $baselineQuery->where('pose', $pose);
        }

        $baseline = (clone $baselineQuery)
            ->where('photo_type', 'baseline')
            ->orderBy('taken_at')
            ->orderBy('id')
            ->first();

        if (!$baseline) {
            $baseline = (clone $baselineQuery)
                ->orderBy('taken_at')
                ->orderBy('id')
                ->first();
        }

        if (!$baseline) {
            return response()->json([
                'message' => 'No photos found for comparison.'
            ], 404);
        }

        $baselineDate = $baseline->taken_at
            ? Carbon::parse($baseline->taken_at)
            : Carbon::parse($baseline->created_at)->startOfDay();

        $targetDate = $baselineDate->copy()->addMonths($months);

        $afterQuery = MemberProgressPhoto::query()
            ->where('member_id', $profile->id)
            ->whereDate('taken_at', '>=', $targetDate->toDateString());

        if ($pose) {
            $afterQuery->where('pose', $pose);
        }

        $after = $afterQuery
            ->orderBy('taken_at')
            ->orderBy('id')
            ->first();

        return response()->json([
            'months' => $months,
            'pose' => $pose,
            'target_date' => $targetDate->toDateString(),
            'before' => [
                'id' => $baseline->id,
                'photo_type' => $baseline->photo_type,
                'pose' => $baseline->pose,
                'notes' => $baseline->notes,
                'taken_at' => optional($baseline->taken_at)->format('Y-m-d'),
                'image_url' => asset('storage/' . $baseline->image_path),
            ],
            'after' => $after ? [
                'id' => $after->id,
                'photo_type' => $after->photo_type,
                'pose' => $after->pose,
                'notes' => $after->notes,
                'taken_at' => optional($after->taken_at)->format('Y-m-d'),
                'image_url' => asset('storage/' . $after->image_path),
            ] : null,
        ]);
    }
}
