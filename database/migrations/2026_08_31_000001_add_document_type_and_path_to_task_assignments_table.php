<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_assignments', function (Blueprint $table) {
            $table->enum('document_type', ['وارد', 'صادر'])->default('وارد')->after('task_number');
            $table->string('document_path')->nullable()->after('document_type');
        });
    }

    public function down(): void
    {
        Schema::table('task_assignments', function (Blueprint $table) {
            $table->dropColumn(['document_type', 'document_path']);
        });
    }
};
