<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('SYSTEM_RECORD', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('USER_FULL_NAME', 50)->nullable();
            $table->string('DEVICE_NAME', 50)->nullable();
            $table->string('MACHINE_IP', 50)->nullable();
            $table->string('TITLE', 100)->nullable();
            $table->string('DESCRIBTION', 200)->nullable();
            $table->date('CREATED_DATE')->nullable();
            $table->string('ISACTIVE', 50)->nullable();
            $table->unsignedBigInteger('USER_ID')->nullable();
            // $timestamps معطلة: الجدول الأصلي معندوش created_at/updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('SYSTEM_RECORD');
    }
};