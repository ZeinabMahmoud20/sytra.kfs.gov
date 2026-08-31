<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_assignments', function (Blueprint $table) {
            $table->id();

            // رقم التكليف - فريد، يُنشأ تلقائياً بصيغة TA-2026-00001 (انظر Model)
            $table->string('task_number')->unique();

            $table->date('received_date');                       // تاريخ الورود
            $table->foreignId('source_id')->constrained('task_sources'); // مصدر التكليف
            $table->foreignId('entity_id')->constrained('task_entities'); // الجهة المختصة للتعامل

            // المسؤول عن المتابعة والتنفيذ (الموظف المكلف)
            $table->foreignId('assignee_id')->constrained('users');

            // مسؤول التكليفات اللي أنشأ/وزّع المهمة
            $table->foreignId('created_by')->constrained('users');

            $table->text('description');                          // وصف التكليف ومتن المذكرة
            $table->enum('priority', ['عالية', 'متوسطة', 'منخفضة'])->default('متوسطة');

            $table->date('deadline');                             // الموعد النهائي
            $table->date('response_date')->nullable();            // موعد الرد الفعلي

            $table->unsignedTinyInteger('completion_percentage')->default(0); // نسبة الإنجاز %

            $table->enum('status', [
                'لم يبدأ',
                'جاري التنفيذ',
                'تم التنفيذ',
                'متأخر',
                'متوقف',
            ])->default('لم يبدأ');

            $table->text('notes')->nullable();                    // ملاحظات (تشمل سبب آخر تغيير حالة)

            $table->timestamp('status_changed_at')->nullable();   // آخر تحديث للحالة
            $table->timestamps();

            $table->index(['status']);
            $table->index(['assignee_id']);
            $table->index(['entity_id']);
            $table->index(['deadline']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assignments');
    }
};
