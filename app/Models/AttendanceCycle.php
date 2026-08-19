<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceCycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_template_id',
        'cycle_number',
    ];

    protected $casts = [
        'cycle_number' => 'integer',
    ];

    public function attendanceTemplate(): BelongsTo
    {
        return $this->belongsTo(AttendanceTemplate::class);
    }

    public function dailyAttendances(): HasMany
    {
        return $this->hasMany(DailyAttendance::class);
    }

    /**
     * الجهات اللي استُخدمت بالفعل خلال الـ Cycle الحالية
     * (عبر كل الـ Daily Attendances اللي فيها)
     */
    public function usedEntityIds(): array
    {
        return DailyAttendanceEntity::whereIn(
            'daily_attendance_id',
            $this->dailyAttendances()->pluck('id')
        )->pluck('entity_id')->unique()->values()->toArray();
    }
}