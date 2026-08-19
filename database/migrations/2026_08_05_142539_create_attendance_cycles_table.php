<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_cycles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('attendance_template_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('cycle_number');

            $table->timestamps();

            $table->unique(
                ['attendance_template_id', 'cycle_number'],
                'attendance_cycles_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_cycles');
    }
};