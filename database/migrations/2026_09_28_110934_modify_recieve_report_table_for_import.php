<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) LOCKED_BY يقبل NULL
        Schema::table('recieve_report', function (Blueprint $table) {
            $table->unsignedBigInteger('LOCKED_BY')->nullable()->default(null)->change();
        });

        // 2) IS_LOCKED له قيمة افتراضية (لو حبيت)
        Schema::table('recieve_report', function (Blueprint $table) {
            $table->boolean('IS_LOCKED')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recieve_report', function (Blueprint $table) {
            $table->unsignedBigInteger('LOCKED_BY')->nullable(false)->change();
            $table->boolean('IS_LOCKED')->default(null)->change();
        });
    }
};
