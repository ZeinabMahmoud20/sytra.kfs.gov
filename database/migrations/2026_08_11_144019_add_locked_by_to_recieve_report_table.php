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
        Schema::table('recieve_report', function (Blueprint $table) {
            //
                        $table->unsignedBigInteger('LOCKED_BY')->nullable()->after('REPORT_END_TIME');
            $table->foreign('LOCKED_BY')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recieve_report', function (Blueprint $table) {
            //
                       $table->dropForeign(['LOCKED_BY']);
            $table->dropColumn('LOCKED_BY');
        });
    }
};
