<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('DEATHS', function (Blueprint $table) {
            $table->bigIncrements('Deceased_ID');
            $table->string('Deceased_NAME', 50)->nullable();
            $table->date('Deceased_AGE')->nullable();
            $table->text('Deceased_ADDRESS')->nullable();
            $table->text('Deceased_FOLLOWUP')->nullable();
            $table->unsignedBigInteger('REPORT_ID')->nullable();
            // $timestamps معطلة: الجدول الأصلي معندوش created_at/updated_at
        });

        Schema::table('DEATHS', function (Blueprint $table) {
            $table->foreign('REPORT_ID')->references('ID')->on('RECIEVE_REPORT');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('DEATHS');
    }
};