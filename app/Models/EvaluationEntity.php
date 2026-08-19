<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationEntity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function monthlyEvaluations(): HasMany
    {
        return $this->hasMany(MonthlyEvaluation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
