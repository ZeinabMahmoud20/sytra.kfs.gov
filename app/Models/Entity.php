<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'main_location',
    ];

    public function attendanceTemplates(): BelongsToMany
    {
        return $this->belongsToMany(AttendanceTemplate::class, 'attendance_template_entities')
            ->withTimestamps();
    }

    public function dailyAttendanceEntities(): HasMany
    {
        return $this->hasMany(DailyAttendanceEntity::class);
    }
}
