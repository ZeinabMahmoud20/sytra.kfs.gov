<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->index('attendance_template_id', 'daily_attendances_attendance_template_id_index');
            $table->dropUnique('daily_attendances_attendance_template_id_attendance_date_unique');
        });

        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->unique(
                ['attendance_template_id', 'attendance_date', 'time_slot'],
                'daily_attendances_template_date_slot_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->dropUnique('daily_attendances_template_date_slot_unique');
        });

        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->unique(
                ['attendance_template_id', 'attendance_date'],
                'daily_attendances_attendance_template_id_attendance_date_unique'
            );
        });

        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->dropIndex('daily_attendances_attendance_template_id_index');
        });
    }
};