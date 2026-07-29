<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('RECIEVE_SIGNAL', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->date('SIGNAL_DATE')->nullable();
            $table->time('SIGNAL_TIME')->nullable();
            $table->string('SIGNAL_SUBJECT', 500)->nullable();
            $table->string('SIGNAL_CONTENT', 500)->nullable();
            $table->string('RECIEVE_CODE', 500)->nullable();
            $table->unsignedBigInteger('AUTHORITY_ID')->nullable();
            // $timestamps معطلة: الجدول الأصلي معندوش created_at/updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('RECIEVE_SIGNAL');
    }
};