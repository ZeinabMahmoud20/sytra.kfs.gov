<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('REPORTING_TYPES', function (Blueprint $table) {
            $table->bigIncrements('REPORT_ID');
            $table->string('REPORT_SORT', 500)->nullable();
            $table->text('AUTHORITY')->nullable();
            $table->boolean('IS_INTERNET')->nullable();
            // $timestamps معطلة: الجدول الأصلي معندوش created_at/updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('REPORTING_TYPES');
    }
};