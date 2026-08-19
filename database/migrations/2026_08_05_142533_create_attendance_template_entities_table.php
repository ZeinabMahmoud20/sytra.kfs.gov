<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_template_entities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('attendance_template_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('entity_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                ['attendance_template_id', 'entity_id'],
                'template_entities_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_template_entities');
    }
};