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

    /**
     * ألوان كل نوع رد - للعرض في شاشات التقييم
     */
    public const STYLES = [
        'head'        => [
            'badge'  => 'bg-green-50 text-green-700 border-green-200',
            'button' => 'bg-green-50 text-green-700 border-green-300 hover:bg-green-100 hover:border-green-500',
            'icon'   => 'fa-user-check',
        ],
        'deputy'      => [
            'badge'  => 'bg-blue-50 text-blue-700 border-blue-200',
            'button' => 'bg-blue-50 text-blue-700 border-blue-300 hover:bg-blue-100 hover:border-blue-500',
            'icon'   => 'fa-user-alt',
        ],
        'operations'  => [
            'badge'  => 'bg-amber-50 text-amber-700 border-amber-200',
            'button' => 'bg-amber-50 text-amber-700 border-amber-300 hover:bg-amber-100 hover:border-amber-500',
            'icon'   => 'fa-satellite-dish',
        ],
        'no_response' => [
            'badge'  => 'bg-red-50 text-red-700 border-red-200',
            'button' => 'bg-red-50 text-red-700 border-red-300 hover:bg-red-100 hover:border-red-500',
            'icon'   => 'fa-times-circle',
        ],
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
