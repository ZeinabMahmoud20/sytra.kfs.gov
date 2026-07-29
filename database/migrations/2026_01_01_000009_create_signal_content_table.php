<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('SIGNAL_CONTENT', function (Blueprint $table) {
            $table->bigIncrements('SIGNALCONTENT_ID');
            $table->string('SIGNALCONTENT', 500)->nullable();
            // $timestamps معطلة: الجدول الأصلي معندوش created_at/updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('SIGNAL_CONTENT');
    }
};