<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Models\SessionBooking;
use App\Models\TrainingSession;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $member = $request->user()->profile;

        $items = SessionBooking::query()
            ->where('member_id', $member->id)
            ->with(['session', 'session.trainer'])
            ->orderByDesc('booked_at')
            ->paginate(20);

        $items->getCollection()->transform(function (SessionBooking $b) {
            return [
                'booking_id' => $b->id,
                'status' => $b->status,
                'booked_at' => $b->booked_at,
                'session' => $b->session ? [
                    'session_id' => $b->session->id,
                    'title' => $b->session->title,
                    'description' => $b->session->description,
                    'session_date' => $b->session->session_date,
                    'duration_minutes' => $b->session->duration_minutes,
                    'trainer' => $b->session->trainer ? [
                        'trainer_id' => $b->session->trainer->id,
                        'full_name' => $b->session->trainer->full_name,
                        'avatar_url' => $b->session->trainer->avatar_url,
                    ] : null,
                ] : null,
            ];
        });

        return response()->json($items);
    }

    public function bookSession(Request $request)
    {
        $request->validate([
            'session_id' => 'required|integer|exists:training_sessions,id',
        ]);

        $member = $request->user()->profile;

        // لازم يكون العضو فعلاً member
        if (!$member || $member->role !== 'member') {
            return response()->json(['message' => 'Unauthorized profile'], 403);
        }

        // جيب الجلسة وتأكد إنها active ومستقبلية
        $session = TrainingSession::query()
            ->where('id', $request->session_id)
            ->where('is_active', 1)
            ->where('session_date', '>=', now())
            ->first();

        if (!$session) {
            return response()->json(['message' => 'Session not found, inactive, or expired'], 404);
        }

        // فحص السعة: booked فقط
        $bookedCount = SessionBooking::query()
            ->where('session_id', $session->id)
            ->where('status', 'booked')
            ->count();

        if ($bookedCount >= $session->max_participants) {
            return response()->json(['message' => 'Session is full'], 409);
        }

        try {
            DB::transaction(function () use ($request, $member, $session) {

                // 1) create booking (unique(session_id, member_id) رح يمنع التكرار)
                SessionBooking::create([
                    'session_id' => $request->session_id,
                    'member_id' => $member->id,
                    'status' => 'booked',
                    'booked_at' => now(),
                ]);

                // 2) ✅ ربط العضو بالمدرب: هذا اللي بخلي العضو يظهر داخل Filament Trainer -> My Members
                // إذا بدك تخلي العضو يقدر يحجز عند مدرب جديد ويغير مدربه: خليها overwrite دائماً
                // إذا بدك "أول مدرب فقط" وما يتغير: استخدم if (is_null($member->trainer_id))
                $member->trainer_id = $session->trainer_id;
                $member->save();
            });
        } catch (QueryException $e) {
            // غالباً unique constraint
            return response()->json(['message' => 'Already booked this session'], 409);
        }

        return response()->json([
            'message' => 'Booked successfully. You are now linked to this trainer.',
        ], 201);
    }

    public function cancelSession(Request $request, $id)
    {
        $member = $request->user()->profile;

        $booking = SessionBooking::query()
            ->where('id', $id)
            ->where('member_id', $member->id)
            ->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        $booking->status = 'cancelled';
        $booking->save();

        return response()->json(['message' => 'Booking cancelled']);
    }
}
