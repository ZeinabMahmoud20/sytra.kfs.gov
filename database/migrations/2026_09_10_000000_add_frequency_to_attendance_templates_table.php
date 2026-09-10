<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_templates', function (Blueprint $table) {
            $table->string('frequency')->default('daily')->after('daily_entities_count');
            $table->json('frequency_config')->nullable()->after('frequency');
            $table->time('second_attendance_time')->nullable()->after('attendance_time');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_templates', function (Blueprint $table) {
            $table->dropColumn(['frequency', 'frequency_config', 'second_attendance_time']);
        });
    }
};
