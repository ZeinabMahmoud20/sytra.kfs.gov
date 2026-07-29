<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('RECIEVE_REPORT', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('REPORTER_SSN', 50)->nullable();
            $table->string('REPORTER_NAME', 50)->nullable();
            $table->string('REPORTING_Auth', 500)->nullable();
            $table->unsignedBigInteger('REPORTING_SORT')->nullable();
            $table->date('REPORT_START_DATE')->nullable();
            $table->time('REPORT_START_TIME')->nullable();
            $table->unsignedBigInteger('REPORT_RECIPIENT')->nullable();
            $table->unsignedBigInteger('CITY')->nullable();
            $table->unsignedBigInteger('VILLAGE')->nullable();
            $table->unsignedBigInteger('X_AXIS')->nullable();
            $table->unsignedBigInteger('Y_AXIS')->nullable();
            $table->text('DAMAGE')->nullable();
            $table->text('PLACE_Accident')->nullable();
            $table->integer('Deceased_Num')->nullable();
            $table->integer('INFECTED_NUM')->nullable();
            $table->date('REPORT_END_DATE')->nullable();
            $table->time('REPORT_END_TIME')->nullable();
            $table->string('REQUEST_STATUS', 50)->nullable();
            $table->boolean('IS_LOCKED')->nullable();
            $table->string('REPORT_REGISTER_NUMBER', 500)->nullable();
            $table->text('REPORT_FOLLOWUP_NUMBER')->nullable();
            $table->text('NOTIFIED_AUTHORITIES')->nullable();
            // $timestamps معطلة: الجدول الأصلي معندوش created_at/updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('RECIEVE_REPORT');
    }
};