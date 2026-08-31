<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskNotificationController extends Controller
{
    /** آخر 10 تنبيهات - تُستخدم في جرس الإشعارات بأعلى الصفحة (AJAX) */
    public function recent()
    {
        $user = Auth::user();

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $user->notifications()->latest()->limit(10)->get()->map(function ($n) {
                return [
                    'id'       => $n->id,
                    'read'     => ! is_null($n->read_at),
                    'title'    => $n->data['title'] ?? '',
                    'task_number' => $n->data['task_number'] ?? '',
                    'description' => $n->data['description'] ?? '',
                    'url'      => $n->data['url'] ?? '#',
                    'created'  => $n->created_at->diffForHumans(),
                ];
            }),
        ]);
    }

    /** لما الموظف يدوس على التنبيه - يتحدد كمقروء ويتوجه مباشرة للتكليف */
    public function open(string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('tasks.index'));
    }

    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['ok' => true]);
    }
}
