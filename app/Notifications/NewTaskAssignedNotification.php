<?php

namespace App\Notifications;

use App\Models\TaskAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * تنبيه داخلي (يظهر في جرس الإشعارات بالتطبيق) بمجرد ما يتسجل تكليف جديد
 * ويتخصص لموظف. مفيش إيميل ولا SMS - تنبيه داخل النظام فقط + بيانات
 * التكليف + رابط يودّي على شاشة التكليف مباشرة.
 */
class NewTaskAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(public TaskAssignment $task)
    {
    }

    public function via($notifiable): array
    {
        // 'database' يخزن التنبيه في جدول notifications عشان يظهر جوه التطبيق
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'task_id'      => $this->task->id,
            'task_number'  => $this->task->task_number,
            'title'        => 'تكليف جديد مُخصص لك',
            'description'  => \Illuminate\Support\Str::limit($this->task->description, 80),
            'priority'     => $this->task->priority,
            'deadline'     => optional($this->task->deadline)->format('Y-m-d'),
            'entity'       => $this->task->entity->name ?? null,
            'source'       => $this->task->source->name ?? null,
            'url'          => route('tasks.show', $this->task->id),
        ];
    }
}
