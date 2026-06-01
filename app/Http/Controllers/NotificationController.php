<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('notifications.index', compact('notifications'));
    }

    public function count(): JsonResponse
    {
        $unread = Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        $recent = Notification::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'title', 'body', 'type', 'read_at', 'created_at']);

        return response()->json(['unread' => $unread, 'recent' => $recent]);
    }

    public function markRead(Notification $notification): RedirectResponse|JsonResponse
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->update(['read_at' => now()]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    public function markAllRead(): RedirectResponse|JsonResponse
    {
        Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }
}
