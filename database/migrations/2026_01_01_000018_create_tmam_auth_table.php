<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TMAM_AUTH', function (Blueprint $table) {
            $table->increments('ID');
            $table->unsignedBigInteger('TMAM_ID')->nullable();
            $table->string('AUTH_ID', 500)->nullable();
            // $timestamps معطلة: الجدول الأصلي معندوش created_at/updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TMAM_AUTH');
    }
};