<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('INJURED', function (Blueprint $table) {
            $table->bigIncrements('INJURED_ID');
            $table->string('INJURED_NAME', 50)->nullable();
            $table->date('INJURED_AGE')->nullable();
            $table->string('INJURED_DIAGNOSIS', 500)->nullable();
            $table->text('INJURED_FOLLOWUP')->nullable();
            $table->unsignedBigInteger('REPORT_ID')->nullable();
            // $timestamps معطلة: الجدول الأصلي معندوش created_at/updated_at
        });

        Schema::table('INJURED', function (Blueprint $table) {
            $table->foreign('REPORT_ID')->references('ID')->on('RECIEVE_REPORT');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('INJURED');
    }
};