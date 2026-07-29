<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('SEND_SIGNAL', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->date('U_SIGNAL_DATE')->nullable();
            $table->time('U_SIGNAL_TIME')->nullable();
            $table->string('U_SIGNAL_SUBJECT', 500)->nullable();
            $table->string('U_SIGNAL_CONTENT', 500)->nullable();
            $table->unsignedBigInteger('AUTHORITY_ID')->nullable();
            $table->unsignedBigInteger('MAIN_SIGNAL_ID')->nullable();
            $table->string('MAIN_SIGNAL_CODE', 500)->nullable();
            // $timestamps معطلة: الجدول الأصلي معندوش created_at/updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('SEND_SIGNAL');
    }
};