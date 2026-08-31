<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskStatusLog extends Model
{
    protected $fillable = [
        'task_assignment_id',
        'changed_by',
        'field_name',
        'old_value',
        'new_value',
        'note',
    ];

    /** ترجمة أسماء الحقول التقنية لعربي مفهوم في شاشة التفاصيل */
    public const FIELD_LABELS = [
        'status'                 => 'الحالة',
        'priority'               => 'الأولوية',
        'assignee_id'            => 'المسؤول عن التنفيذ',
        'deadline'                => 'الموعد النهائي',
        'response_date'          => 'موعد الرد',
        'completion_percentage'  => 'نسبة الإنجاز',
        'entity_id'              => 'الجهة المختصة',
        'description'             => 'وصف التكليف',
    ];

    public function taskAssignment(): BelongsTo
    {
        return $this->belongsTo(TaskAssignment::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'changed_by');
    }

    public function getFieldLabelAttribute(): string
    {
        return self::FIELD_LABELS[$this->field_name] ?? $this->field_name;
    }
}
