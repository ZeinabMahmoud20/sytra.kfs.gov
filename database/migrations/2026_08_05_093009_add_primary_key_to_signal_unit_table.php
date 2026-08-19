<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
                DB::statement('ALTER TABLE `SIGNAL_UNIT` MODIFY `ID` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `SIGNAL_UNIT` DROP PRIMARY KEY, MODIFY `ID` BIGINT UNSIGNED NOT NULL');

    }
};
