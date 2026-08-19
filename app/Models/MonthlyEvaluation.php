<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_entity_id',
        'year',
        'month',
        'total_score',
        'max_possible_score',
        'percentage',
        'grade_out_of_20',
        'rank',
    ];

    protected $casts = [
        'percentage'      => 'decimal:2',
        'grade_out_of_20' => 'decimal:2',
    ];

    public function entity(): BelongsTo
    {
        return $this->belongsTo(EvaluationEntity::class, 'evaluation_entity_id');
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }
}
