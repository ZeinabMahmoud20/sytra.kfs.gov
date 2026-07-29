<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TMAM_ALARM', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('Tmam_Name', 500)->nullable();
            $table->time('Tmam_Time')->nullable();
            $table->string('Tmam_State', 50)->nullable();
            // $timestamps معطلة: الجدول الأصلي معندوش created_at/updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TMAM_ALARM');
    }
};