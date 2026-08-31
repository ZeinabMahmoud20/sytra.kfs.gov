<?php

namespace App\Models;

use App\Notifications\NewTaskAssignedNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class TaskAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_number',
        'document_type',
        'document_path',
        'received_date',
        'source_id',
        'entity_id',
        'assignee_id',
        'created_by',
        'description',
        'priority',
        'deadline',
        'response_date',
        'completion_percentage',
        'status',
        'notes',
        'status_changed_at',
    ];

    protected $casts = [
        'received_date'     => 'date',
        'deadline'          => 'date',
        'response_date'     => 'date',
        'status_changed_at' => 'datetime',
    ];

    /** أيقونة الحالة - مطابقة لنظام الألوان في الإكسل الأصلي */
    public const STATUS_ICONS = [
        'تم التنفيذ'    => '🟢',
        'جاري التنفيذ'  => '🟡',
        'متأخر'         => '🟠',
        'لم يبدأ'       => '🔴',
        'متوقف'         => '⚫',
    ];

    // ------------------------------------------------------------
    // العلاقات
    // ------------------------------------------------------------

    public function source(): BelongsTo
    {
        return $this->belongsTo(TaskSource::class, 'source_id');
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(TaskEntity::class, 'entity_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assignee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TaskStatusLog::class)->latest();
    }

    // ------------------------------------------------------------
    // Accessors محسوبة (زي أعمدة المعادلات في الإكسل)
    // ------------------------------------------------------------

    /** أيام التأخير - بناءً على موعد الرد الفعلي مقابل الموعد النهائي (حسب اتفاقنا) */
    public function getDelayDaysAttribute(): int
    {
        $reference = $this->response_date ?? Carbon::today();

        if (! $this->deadline || $this->status === 'تم التنفيذ') {
            // لو خلص قبل أو في الميعاد، مفيش تأخير
            if ($this->status === 'تم التنفيذ' && $this->response_date && $this->response_date->gt($this->deadline)) {
                return $this->deadline->diffInDays($this->response_date);
            }
            return 0;
        }

        if ($reference->gt($this->deadline)) {
            return $this->deadline->diffInDays($reference);
        }

        return 0;
    }

    public function getStatusIconAttribute(): string
    {
        return self::STATUS_ICONS[$this->status] ?? '';
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->delay_days > 0 && $this->status !== 'تم التنفيذ';
    }

    // ------------------------------------------------------------
    // توليد رقم تكليف تلقائي: TA-2026-00001
    // ------------------------------------------------------------

    public static function generateTaskNumber(): string
    {
        $year = now()->year;
        $last = static::whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $nextSeq = $last
            ? ((int) substr($last->task_number, -5)) + 1
            : 1;

        return sprintf('TA-%d-%05d', $year, $nextSeq);
    }

    // ------------------------------------------------------------
    // تسجيل تعديل + تنبيه (تُستدعى من الـ Controller)
    // ------------------------------------------------------------

    public function logChange(string $field, $old, $new, ?int $changedBy = null, ?string $note = null): void
    {
        if ((string) $old === (string) $new) {
            return;
        }

        $this->logs()->create([
            'changed_by' => $changedBy ?? auth()->id(),
            'field_name' => $field,
            'old_value'  => $old,
            'new_value'  => $new,
            'note'       => $note,
        ]);
    }

    public function notifyAssignee(): void
    {
        $this->assignee?->notify(new NewTaskAssignedNotification($this));
    }
}
