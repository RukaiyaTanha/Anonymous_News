<?php

namespace App\Http\Controllers;

use App\Models\Notification;

class NotificationController extends Controller
{
	public function index()
	{
		Notification::where('user_id', auth()->id())
			->where('is_read', false)
			->update(['is_read' => true]);

		$notifications = Notification::where('user_id', auth()->id())
			->latest('created_at')
			->paginate(12);

		$unreadCount = 0;

		return view('notifications.index', compact('notifications', 'unreadCount'));
	}

	public function markAsRead(Notification $notification)
	{
		if ($notification->user_id !== auth()->id()) {
			abort(403);
		}

		$notification->update(['is_read' => true]);

		return back();
	}

	public function markAllAsRead()
	{
		Notification::where('user_id', auth()->id())
			->where('is_read', false)
			->update(['is_read' => true]);

		return back();
	}
}
