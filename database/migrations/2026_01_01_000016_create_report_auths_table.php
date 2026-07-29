<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('REPORT_AUTHS', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->unsignedBigInteger('REPORT_ID')->nullable();
            $table->text('AUTHORITY_ID')->nullable();
            // $timestamps معطلة: الجدول الأصلي معندوش created_at/updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('REPORT_AUTHS');
    }
};