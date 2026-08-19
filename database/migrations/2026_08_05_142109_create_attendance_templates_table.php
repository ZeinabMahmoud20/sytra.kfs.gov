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
        Schema::create('attendance_templates', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->time('attendance_time');

            $table->text('script');

            $table->unsignedInteger('daily_entities_count');

            $table->boolean('is_active')->default(true);

            $table->timestamps();                                                                                   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_templates');
    }
};
