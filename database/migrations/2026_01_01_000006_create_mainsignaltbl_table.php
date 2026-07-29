<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('MainSignalTBL', function (Blueprint $table) {
            $table->bigIncrements('MainSignalID');
            $table->string('MainSignalCode', 500)->nullable();
            $table->unsignedBigInteger('RECEIVER_ID')->nullable();
            // $timestamps معطلة: الجدول الأصلي معندوش created_at/updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('MainSignalTBL');
    }
};