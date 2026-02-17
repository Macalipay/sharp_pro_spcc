<?php

namespace App\Http\Controllers;

use App\Notifications;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    /**
     * @var NotificationService
     */
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $user = Auth::user();
        $notifications = $this->notificationService->forUserPaginated($user, 'header', 15);

        return view('backend.pages.notifications.index', compact('notifications'), ['type' => 'full-view']);
    }

    public function markAsRead($id)
    {
        $notification = Notifications::findOrFail($id);
        $user = Auth::user();

        if (!$notification->isForUser($user)) {
            abort(403);
        }

        if (!$notification->is_read) {
            $notification->is_read = true;
            $notification->seen_at = $notification->seen_at ?: now();
            $notification->read_at = now();
            $notification->save();
        }

        return redirect($notification->target_url);
    }

    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        $notifications = $this->notificationService->forUser($user, 'header');

        $ids = $notifications->pluck('id')->all();
        if (!empty($ids)) {
            Notifications::whereIn('id', $ids)->update([
                'is_read' => true,
                'seen_at' => now(),
                'read_at' => now(),
            ]);
        }

        return redirect()->back();
    }
}
