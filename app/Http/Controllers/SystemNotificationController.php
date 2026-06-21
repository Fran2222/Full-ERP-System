<?php

namespace App\Http\Controllers;

use App\Models\SystemNotification;
use Illuminate\Http\Request;

class SystemNotificationController extends Controller
{
    public function index()
    {
        $notifications = SystemNotification::where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function open(SystemNotification $notification)
    {
        abort_unless((int) $notification->user_id === (int) auth()->id(), 403);

        $notification->markAsRead();

        return redirect($notification->action_url ?: route('system-notifications.index'));
    }

    public function markRead(SystemNotification $notification)
    {
        abort_unless((int) $notification->user_id === (int) auth()->id(), 403);

        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead(Request $request)
    {
        SystemNotification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function poll()
    {
        $userId = auth()->id();

        if (! $userId) {
            return response()->json([
                'unread_count' => 0,
                'badge' => '',
                'notifications' => [],
            ]);
        }

        $notifications = \App\Services\SystemNotificationService::latestForUser($userId, 5);
        $unreadCount = \App\Services\SystemNotificationService::unreadCountForUser($userId);

        return response()->json([
            'unread_count' => $unreadCount,
            'badge' => $unreadCount > 99 ? '99+' : (string) $unreadCount,
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => (string) $notification->title,
                    'message' => (string) $notification->message,
                    'is_read' => (bool) $notification->is_read,
                    'created_at_human' => optional($notification->created_at)->diffForHumans(),
                    'open_url' => url('/notifications/' . $notification->id . '/open'),
                ];
            })->values(),
        ]);
    }
}