<?php

namespace App\Http\Controllers\Api\Trainer;

use App\Http\Controllers\Controller;
use App\Models\SessionBooking;
use Illuminate\Http\Request;

class TrainerBookingsController extends Controller
{
    public function index(Request $request)
    {
        $trainer = $request->user()->profile;

        $items = SessionBooking::query()
            ->whereHas('session', fn ($q) => $q->where('trainer_id', $trainer->id))
            ->with(['session', 'member'])
            ->orderByDesc('booked_at')
            ->paginate(30);

        $items->getCollection()->transform(function (SessionBooking $b) {
            return [
                'booking_id' => $b->id,
                'status' => $b->status,
                'booked_at' => $b->booked_at,
                'member' => $b->member ? [
                    'member_id' => $b->member->id,
                    'full_name' => $b->member->full_name,
                    'avatar_url' => $b->member->avatar_url,
                ] : null,
                'session' => $b->session ? [
                    'session_id' => $b->session->id,
                    'title' => $b->session->title,
                    'session_date' => $b->session->session_date,
                    'duration_minutes' => $b->session->duration_minutes,
                ] : null,
            ];
        });

        return response()->json($items);
    }
}
