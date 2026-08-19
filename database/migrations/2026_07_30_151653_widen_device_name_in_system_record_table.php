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
        Schema::table('SYSTEM_RECORD', function (Blueprint $table) {
            //
             $table->string('DEVICE_NAME', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('SYSTEM_RECORD', function (Blueprint $table) {
            //
              $table->string('DEVICE_NAME', 50)->nullable()->change();
        });
    }
};
