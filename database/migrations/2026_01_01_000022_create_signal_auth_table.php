<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('SIGNAL_AUTH', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('CONTACT_NAME', 500)->nullable();
            $table->string('STATE', 50)->nullable();
            $table->unsignedBigInteger('SIGNAL_ID')->nullable();
            $table->unsignedBigInteger('MAIN_SIGNAL_ID')->nullable();
            $table->string('MAIN_SIGNAL_CODE', 50)->nullable();
            // $timestamps معطلة: الجدول الأصلي معندوش created_at/updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('SIGNAL_AUTH');
    }
};