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
        Schema::table('contact_guides', function (Blueprint $table) {
            $table->text('department_name')->change();
            $table->text('manager_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_guides', function (Blueprint $table) {
            $table->string('department_name')->change();
            $table->string('manager_name')->nullable()->change();
        });
    }
};
