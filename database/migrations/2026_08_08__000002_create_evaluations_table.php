<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول التقييمات اليومية لكل جهة.
     * قيد Unique على (evaluation_entity_id, evaluation_date) يمنع تكرار
     * تقييم نفس الجهة في نفس اليوم من أي حد من الاتنين المسئولين.
     */
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('evaluation_entity_id')
                ->constrained('evaluation_entities')
                ->cascadeOnDelete();

            $table->foreignId('evaluated_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->date('evaluation_date');

            // نوع الرد: رئيس الجهة (3) / نائب (2) / عمليات (1) / لم يتم الرد (0)
            $table->enum('response_type', ['head', 'deputy', 'operations', 'no_response']);

            // النقطة محسوبة تلقائيًا من response_type وقت الحفظ (0 إلى 3)
            $table->unsignedTinyInteger('score');

            $table->text('note')->nullable(); // ملاحظة اختيارية (مثلاً سبب عدم الرد)

            $table->timestamps();

            // منع تكرار تقييم نفس الجهة في نفس اليوم
            $table->unique(['evaluation_entity_id', 'evaluation_date'], 'uniq_entity_per_day');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
