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
        Schema::create('daily_attendance_entities', function (Blueprint $table) {
            $table->id();
        $table->foreignId('daily_attendance_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('entity_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->enum('status', [
            'pending',
            'done',
            'not_done'
        ])->default('pending');

        $table->timestamp('response_at')->nullable();

        $table->timestamps();

        $table->unique([
            'daily_attendance_id',
            'entity_id'
        ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_attendance_entities');
    }
};
