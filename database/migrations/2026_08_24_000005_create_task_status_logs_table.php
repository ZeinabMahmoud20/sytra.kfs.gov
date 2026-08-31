<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * سجل كامل (Audit Trail) لكل تعديل يحصل على أي تكليف:
 * مين عدّل، إمتى، وإيه اللي اتغيّر بالظبط (قبل/بعد).
 * ده اللي بيظهر في نافذة "تفاصيل" التكليف.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_assignment_id')->constrained('task_assignments')->cascadeOnDelete();
            $table->foreignId('changed_by')->constrained('users');

            $table->string('field_name');      // مثال: status, priority, assignee_id, deadline...
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->text('note')->nullable();  // ملاحظة/سبب التعديل لو موجودة

            $table->timestamps();

            $table->index(['task_assignment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_status_logs');
    }
};
