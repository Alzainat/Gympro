<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\TrainingSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrainerDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $trainers = Profile::query()
            ->where('role', 'trainer')
            ->with(['trainerProfile', 'user'])
            ->orderByDesc(DB::raw('COALESCE((select rating from trainer_profiles where trainer_profiles.profile_id = profiles.id), 0)'))
            ->paginate(12);

        $trainers->getCollection()->transform(function (Profile $p) {
            return [
                'id' => $p->id,
                'trainer_id' => $p->id,
                'full_name' => $p->full_name,
                'avatar_url' => $p->avatar_url,
                'bio' => $p->bio,
                'email' => $p->user?->email,
                'rating' => $p->trainerProfile?->rating,
                'review_count' => $p->trainerProfile?->review_count,
                'hourly_rate' => $p->trainerProfile?->hourly_rate,
                'is_available' => $p->trainerProfile?->is_available,
                'specializations' => $p->trainerProfile?->specializations,
            ];
        });

        return response()->json($trainers);
    }

    public function show(Request $request, $trainerId)
    {
        $trainer = Profile::query()
            ->where('role', 'trainer')
            ->where('id', $trainerId)
            ->with(['trainerProfile', 'user'])
            ->first();

        if (!$trainer) {
            return response()->json([
                'message' => 'Trainer not found'
            ], 404);
        }

        return response()->json([
            'id' => $trainer->id,
            'trainer_id' => $trainer->id,
            'full_name' => $trainer->full_name,
            'avatar_url' => $trainer->avatar_url,
            'bio' => $trainer->bio,
            'email' => $trainer->user?->email,
            'rating' => $trainer->trainerProfile?->rating,
            'review_count' => $trainer->trainerProfile?->review_count,
            'hourly_rate' => $trainer->trainerProfile?->hourly_rate,
            'is_available' => $trainer->trainerProfile?->is_available,
            'specializations' => $trainer->trainerProfile?->specializations,
            'certification_url' => $trainer->trainerProfile?->certification_url,
            'work_schedule' => $trainer->trainerProfile?->work_schedule,
        ]);
    }

    public function sessions(Request $request, $trainerId)
    {
        $sessions = TrainingSession::query()
            ->where('trainer_id', $trainerId)
            ->where('is_active', 1)
            // ->where('session_date', '>=', now())
            ->withCount([
                'bookings as booked_count' => function ($q) {
                    $q->where('status', 'booked');
                }
            ])
            ->orderBy('session_date')
            ->get()
            ->filter(fn ($s) => $s->booked_count < $s->max_participants)
            ->values();

        return response()->json([
            'data' => $sessions
        ]);
    }

    public function schedule(Request $request, $trainerId)
    {
        $items = DB::table('trainer_availability')
            ->where('trainer_id', $trainerId)
            ->where('is_available', 1)
            ->orderByRaw("FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
            ->get();

        return response()->json($items);
    }
}
