<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('USER', function (Blueprint $table) {
            $table->bigIncrements('USER_ID');
            $table->string('USER_NAME', 50)->nullable();
            $table->string('USER_PASSWORD', 50)->nullable();
            $table->string('USER_TYPE', 50)->nullable();
            $table->string('EMPLOYEE_FULL_NAME', 50)->nullable();
            $table->string('E_NATIONAL_ID', 50)->nullable();
            $table->string('PHONE_NUMBER', 50)->nullable();
            $table->text('JOB_TITLE')->nullable();
            $table->string('JOB_GRADE', 50)->nullable();
            $table->text('USER_PICTURE')->nullable();
            $table->binary('Picture')->nullable();
            // $timestamps معطلة: الجدول الأصلي معندوش created_at/updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('USER');
    }
};