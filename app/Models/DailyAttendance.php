<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_template_id',
        'attendance_cycle_id',
        'attendance_date',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function attendanceTemplate(): BelongsTo
    {
        return $this->belongsTo(AttendanceTemplate::class);
    }

    public function attendanceCycle(): BelongsTo
    {
        return $this->belongsTo(AttendanceCycle::class);
    }

    public function dailyAttendanceEntities(): HasMany
    {
        return $this->hasMany(DailyAttendanceEntity::class);
    }
}