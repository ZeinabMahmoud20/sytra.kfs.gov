<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('DAILY_TMAM', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('CODE')->nullable();
            $table->date('TMAM_DATE')->nullable();
            $table->time('TMAM_TIME')->nullable();
            $table->unsignedBigInteger('TMAM_ID')->nullable();
            // $timestamps معطلة: الجدول الأصلي معندوش created_at/updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('DAILY_TMAM');
    }
};