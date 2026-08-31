<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * الجهات/المراكز المختصة بالتعامل مع التكليفات (قائمة الـ 14 مركز/مدينة +
 * الإدارات المذكورة في ورقة "قوائم الإعداد" بملف الإكسل).
 *
 * ملحوظة: منفصل تماماً عن App\Models\Entity المستخدم في نظام الحضور
 * والانصراف عندكم - عشان محدش يتأثر بتعديلات الموديول ده.
 */
class TaskEntity extends Model
{
    use HasFactory;

    protected $table = 'task_entities';

    protected $fillable = [
        'name',
        'type',
        'is_active',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(TaskAssignment::class, 'entity_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
