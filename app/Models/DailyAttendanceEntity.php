<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyAttendanceEntity extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_attendance_id',
        'entity_id',
        'status',
        'response_at',
    ];

    protected $casts = [
        'response_at' => 'datetime',
    ];

    public function dailyAttendance(): BelongsTo
    {
        return $this->belongsTo(DailyAttendance::class);
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function markDone(): void
    {
        $this->update([
            'status' => 'done',
            'response_at' => now(),
        ]);
    }

    public function markNotDone(): void
    {
        $this->update([
            'status' => 'not_done',
            'response_at' => now(),
        ]);
    }
}