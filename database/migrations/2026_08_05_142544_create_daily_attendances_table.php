<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_attendances', function (Blueprint $table) {
            $table->id(); // <-- ناقصة دي
    
        $table->foreignId('attendance_template_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('attendance_cycle_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->date('attendance_date');

        $table->enum('status', [
            'created',
            'waiting',
            'in_progress',
            'completed'
        ])->default('created');

        $table->timestamp('started_at')->nullable();

        $table->timestamp('completed_at')->nullable();

        $table->timestamps();

        $table->unique([
            'attendance_template_id',
            'attendance_date'
        ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_attendances');
    }
};
