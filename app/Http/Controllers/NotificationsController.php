<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationsController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->where('type', 'not like', '%Admin%')
            ->latest()
            ->paginate(20);

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    public function latest(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->where('type', 'not like', '%Admin%')
            ->latest()
            ->take(10)
            ->get();

        $unreadCount = $request->user()
            ->unreadNotifications()
            ->where('type', 'not like', '%Admin%')
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->where('type', 'not like', '%Admin%')
            ->findOrFail($id);

        $notification->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()
            ->unreadNotifications()
            ->where('type', 'not like', '%Admin%')
            ->get()
            ->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }
}
