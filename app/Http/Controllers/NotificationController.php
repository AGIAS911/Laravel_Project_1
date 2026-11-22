<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $unreadOnly = $request->query('filter') === 'unread';
        $query = $unreadOnly ? $user->unreadNotifications() : $user->notifications();
        $notifications = $query->paginate(20);

        if ($unreadOnly) {
            $notifications->appends(['filter' => 'unread']);
        }

        if ($notifications->currentPage() > $notifications->lastPage()) {
            return redirect($notifications->url($notifications->lastPage()));
        }

        $unreadCount = $unreadOnly ? $notifications->total() : $user->unreadNotifications()->count();

        return view('notifications.index', compact('notifications', 'unreadOnly', 'unreadCount'));
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->reorder()->update(['read_at' => now()]);

        return back();
    }

    public function markAsRead(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return back();
    }
}
