<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('VILLAGE', function (Blueprint $table) {
            $table->bigIncrements('VILLAGE_ID');
            $table->string('VILLAGE_NAME', 50)->nullable();
            $table->string('LOCAL_UNIT', 50)->nullable();
            $table->unsignedBigInteger('CITY_ID')->nullable();
            $table->unsignedBigInteger('FOREIGN_VILLAGE_ID')->nullable();
            $table->unsignedBigInteger('X_AXIS')->nullable();
            $table->unsignedBigInteger('Y_AXIS')->nullable();
            $table->string('VILLAGE_SORT', 50)->nullable();
            // $timestamps معطلة: الجدول الأصلي معندوش created_at/updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('VILLAGE');
    }
};