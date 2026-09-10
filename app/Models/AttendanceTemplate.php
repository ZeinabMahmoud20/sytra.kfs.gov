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
        'second_attendance_time',
        'script',
        'daily_entities_count',
        'frequency',
        'frequency_config',
        'is_active',
    ];

    protected $casts = [
        'attendance_time' => 'datetime:H:i',
        'second_attendance_time' => 'datetime:H:i',
        'daily_entities_count' => 'integer',
        'frequency_config' => 'array',
        'is_active' => 'boolean',
    ];

    const FREQUENCY_DAILY = 'daily';
    const FREQUENCY_TWICE_DAILY = 'twice_daily';
    const FREQUENCY_WEEKLY = 'weekly';
    const FREQUENCY_MONTHLY = 'monthly';
    const FREQUENCY_CUSTOM_DATES = 'custom_dates';

    const FREQUENCY_LABELS = [
        self::FREQUENCY_DAILY => 'يومي',
        self::FREQUENCY_TWICE_DAILY => 'مرتين في اليوم',
        self::FREQUENCY_WEEKLY => 'أسبوعي',
        self::FREQUENCY_MONTHLY => 'شهري',
        self::FREQUENCY_CUSTOM_DATES => 'بتاريخ دوري',
    ];

    public function shouldRunToday(): bool
    {
        $today = now();
        $dayOfWeek = $today->dayOfWeek; // 0=Sunday, 6=Saturday
        $dayOfMonth = $today->day;

        return match ($this->frequency) {
            self::FREQUENCY_DAILY => true,
            self::FREQUENCY_TWICE_DAILY => true,
            self::FREQUENCY_WEEKLY => in_array($dayOfWeek, $this->frequency_config['days_of_week'] ?? []),
            self::FREQUENCY_MONTHLY => $dayOfMonth == ($this->frequency_config['day_of_month'] ?? null),
            self::FREQUENCY_CUSTOM_DATES => in_array($today->toDateString(), $this->frequency_config['custom_dates'] ?? []),
            default => true,
        };
    }

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