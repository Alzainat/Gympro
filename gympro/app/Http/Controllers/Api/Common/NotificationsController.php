<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->notifications()->latest()->paginate(20)
        );
    }

    public function unread(Request $request)
    {
        return response()->json(
            $request->user()->unreadNotifications()->latest()->paginate(20)
        );
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $n = $request->user()->notifications()->where('id', $id)->first();
        if (!$n) return response()->json(['message' => 'Notification not found'], 404);

        $n->markAsRead();
        return response()->json(['message' => 'Marked as read']);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'All notifications marked as read']);
    }
}
