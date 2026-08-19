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
    Schema::table('users', function (Blueprint $table) {
        $table->string('employee_full_name')->nullable()->after('name');
        $table->string('national_id')->nullable()->after('employee_full_name');
        $table->string('phone_number')->nullable()->after('national_id');
        $table->string('job_title')->nullable()->after('phone_number');
        $table->string('job_grade')->nullable()->after('job_title');
        $table->string('user_type')->nullable()->after('job_grade');
        $table->string('picture_path')->nullable()->after('user_type');
    });
}

    /**
     * Reverse the migrations.
     */
public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn([
            'employee_full_name', 'national_id', 'phone_number',
            'job_title', 'job_grade', 'user_type', 'picture_path',
        ]);
    });
}
};
