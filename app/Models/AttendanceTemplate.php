<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'attendance_time',
        'script',
        'daily_entities_count',
        'is_active',
    ];

    protected $casts = [
        'attendance_time' => 'datetime:H:i',
        'daily_entities_count' => 'integer',
        'is_active' => 'boolean',
    ];

    public function entities(): BelongsToMany
    {
        return $this->belongsToMany(Entity::class, 'attendance_template_entities')
            ->withTimestamps();
    }

    public function cycles(): HasMany
    {
        return $this->hasMany(AttendanceCycle::class);
    }

    public function dailyAttendances(): HasMany
    {
        return $this->hasMany(DailyAttendance::class);
    }

    public function currentCycle(): ?AttendanceCycle
    {
        return $this->cycles()->latest('cycle_number')->first();
    }
}