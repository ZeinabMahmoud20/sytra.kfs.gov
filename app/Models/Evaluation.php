<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation extends Model
{
    use HasFactory;

    /**
     * خريطة نوع الرد إلى النقاط - المصدر الوحيد للحقيقة (Single Source of Truth)
     * عشان أي حد يستخدمها في أي مكان بدل ما يكررها.
     */
    public const SCORES = [
        'head'        => 3, // رئيس الجهة رد
        'deputy'      => 2, // نائب عنه رد
        'operations'  => 1, // عمليات رد
        'no_response' => 0, // لم يتم الرد
    ];

    public const LABELS = [
        'head'        => 'رئيس الجهة',
        'deputy'      => 'نائب عن الرئيس',
        'operations'  => 'عمليات',
        'no_response' => 'لم يتم الرد',
    ];

    protected $fillable = [
        'evaluation_entity_id',
        'evaluated_by',
        'evaluation_date',
        'response_type',
        'score',
        'note',
    ];

    protected $casts = [
        'evaluation_date' => 'date',
    ];

    protected static function booted(): void
    {
        // احسب النقطة تلقائيًا من نوع الرد وقت الإنشاء/التعديل
        static::saving(function (Evaluation $evaluation) {
            $evaluation->score = self::SCORES[$evaluation->response_type] ?? 0;
        });
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(EvaluationEntity::class, 'evaluation_entity_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}
