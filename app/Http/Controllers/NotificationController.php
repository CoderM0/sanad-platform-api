<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function get_user_notifications()
    {
        $notifications = Auth::user()->notifications;

        return response()->json($notifications);
    }
    public function mark_as_read($id)
    {
        $user = User::find(Auth::user()->id);
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['message' => 'تم تحديد الإشعار كمقروء']);
    }
    public function read_all()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'تم تحديد جميع الإشعارات كمقروءة']);
    }
    public function delete_notification($id)
    {
        $user = User::find(Auth::user()->id);

        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json(['message' => 'تم حذف الإشعار']);
    }
    public function delete_all_notifications()
    {
        $user = User::find(Auth::user()->id);

        $user->notifications()->delete();

        return response()->json(['message' => 'تم حذف جميع الإشعارات']);
    }
    public function unread_notifications()
    {
        return Auth::user()->unreadNotifications;
    }
}
