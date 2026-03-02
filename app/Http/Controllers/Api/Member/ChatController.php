<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Profile;
use App\Models\SessionBooking;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    private function assertCanChat(Profile $member, int $trainerId): void
    {
        // لازم target يكون trainer
        $trainer = Profile::query()
            ->where('id', $trainerId)
            ->where('role', 'trainer')
            ->firstOrFail();

        // ✅ Rule 1: linked trainer_id
        if ((int) $member->trainer_id === (int) $trainer->id) {
            return;
        }

        // ✅ Rule 2: has booking with this trainer
        $hasBooking = SessionBooking::query()
            ->where('member_id', $member->id)
            ->whereIn('status', ['booked', 'attended'])
            ->whereHas('session', fn ($q) => $q->where('trainer_id', $trainer->id))
            ->exists();

        if (! $hasBooking) {
            abort(403, 'You can only chat with the trainer you booked with.');
        }
    }

    // ✅ GET /member/chat/allowed-trainers
    public function allowedTrainers(Request $request)
    {
        $member = $request->user()->profile;

        // trainers from bookings
        $fromBookings = Profile::query()
            ->where('role', 'trainer')
            ->whereIn('id', function ($sub) use ($member) {
                $sub->select('training_sessions.trainer_id')
                    ->from('session_bookings')
                    ->join('training_sessions', 'training_sessions.id', '=', 'session_bookings.session_id')
                    ->where('session_bookings.member_id', $member->id)
                    ->whereIn('session_bookings.status', ['booked', 'attended']);
            });

        // trainer_id direct
        $fromLink = Profile::query()
            ->where('role', 'trainer')
            ->when($member->trainer_id, fn ($q) => $q->where('id', $member->trainer_id));

        $trainers = $fromBookings->union($fromLink)->get()
            ->map(fn ($t) => [
                'trainer_id' => $t->id,
                'full_name' => $t->full_name,
                'avatar_url' => $t->avatar_url,
            ])
            ->values();

        return response()->json($trainers);
    }

    // ✅ GET /member/chat/thread/{trainerId}
    public function thread(Request $request, $trainerId)
    {
        $member = $request->user()->profile;
        $trainerId = (int) $trainerId;

        $this->assertCanChat($member, $trainerId);

        $items = Message::query()
            ->where(function ($q) use ($member, $trainerId) {
                $q->where('sender_id', $member->id)->where('receiver_id', $trainerId);
            })
            ->orWhere(function ($q) use ($member, $trainerId) {
                $q->where('sender_id', $trainerId)->where('receiver_id', $member->id);
            })
            ->orderBy('sent_at', 'asc')
            ->get();

        return response()->json($items);
    }

    // ✅ POST /member/chat/send  (receiver_id = trainer_id)
    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|integer|exists:profiles,id',
            'content' => 'required|string|max:5000',
        ]);

        $member = $request->user()->profile;
        $trainerId = (int) $request->receiver_id;

        $this->assertCanChat($member, $trainerId);

        $msg = Message::create([
            'sender_id' => $member->id,
            'receiver_id' => $trainerId,
            'content' => $request->content,
            'sent_at' => now(),
            'is_read' => false,
        ]);

        return response()->json($msg, 201);
    }

    // ✅ GET /member/chat/inbox  (آخر رسالة لكل trainer + unread)
    public function inbox(Request $request)
    {
        $member = $request->user()->profile;

        // trainers المسموحين
        $allowed = $this->allowedTrainers($request)->getData(true);
        $trainerIds = collect($allowed)->pluck('trainer_id')->map(fn ($x) => (int)$x)->values();

        if ($trainerIds->isEmpty()) return response()->json([]);

        // آخر رسالة لكل محادثة (member-trainer)
        $lastMessages = Message::query()
            ->where(function ($q) use ($member, $trainerIds) {
                $q->where('sender_id', $member->id)->whereIn('receiver_id', $trainerIds);
            })
            ->orWhere(function ($q) use ($member, $trainerIds) {
                $q->whereIn('sender_id', $trainerIds)->where('receiver_id', $member->id);
            })
            ->orderByDesc('sent_at')
            ->get()
            ->groupBy(function ($m) use ($member) {
                // key = trainerId
                return (int)($m->sender_id === $member->id ? $m->receiver_id : $m->sender_id);
            })
            ->map(fn ($grp) => $grp->first())
            ->values();

        // unread count لكل trainer (رسائل جاية للعضو)
        $unread = Message::query()
            ->where('receiver_id', $member->id)
            ->whereIn('sender_id', $trainerIds)
            ->where('is_read', false)
            ->selectRaw('sender_id, COUNT(*) as c')
            ->groupBy('sender_id')
            ->pluck('c', 'sender_id');

        // shape احترافي للفرونت
        $trainerMap = collect($allowed)->keyBy('trainer_id');

        $out = $lastMessages->map(function ($m) use ($member, $trainerMap, $unread) {
            $trainerId = (int)($m->sender_id === $member->id ? $m->receiver_id : $m->sender_id);
            $t = $trainerMap->get($trainerId);

            return [
                'trainer' => $t,
                'last_message' => [
                    'id' => $m->id,
                    'sender_id' => $m->sender_id,
                    'receiver_id' => $m->receiver_id,
                    'content' => $m->content,
                    'sent_at' => $m->sent_at,
                    'is_read' => (bool)$m->is_read,
                ],
                'unread_count' => (int) ($unread[$trainerId] ?? 0),
            ];
        });

        return response()->json($out);
    }

    // ✅ POST /member/chat/{trainerId}/read
    public function markRead(Request $request, $trainerId)
    {
        $member = $request->user()->profile;
        $trainerId = (int) $trainerId;

        $this->assertCanChat($member, $trainerId);

        Message::query()
            ->where('sender_id', $trainerId)
            ->where('receiver_id', $member->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'Marked as read']);
    }
}
