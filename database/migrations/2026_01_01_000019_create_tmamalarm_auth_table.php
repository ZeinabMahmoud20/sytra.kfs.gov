<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TMAMALARM_AUTH', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->unsignedBigInteger('TMAM_ID')->nullable();
            $table->string('USER_ID', 500)->nullable();
            // $timestamps معطلة: الجدول الأصلي معندوش created_at/updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TMAMALARM_AUTH');
    }
};