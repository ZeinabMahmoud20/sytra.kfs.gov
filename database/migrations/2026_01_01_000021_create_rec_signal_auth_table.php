<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('REC_SIGNAL_AUTH', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('CONTACT_NAME', 500)->nullable();
            $table->string('STATE', 500)->nullable();
            $table->unsignedBigInteger('SIGNAL_ID')->nullable();
            // $timestamps معطلة: الجدول الأصلي معندوش created_at/updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('REC_SIGNAL_AUTH');
    }
};