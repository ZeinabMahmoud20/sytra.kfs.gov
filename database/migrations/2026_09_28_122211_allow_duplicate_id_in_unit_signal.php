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
       // 1) شيل AUTO_INCREMENT أولًا (MySQL مش بيسمح بحذف الـ primary key وهو موجود)
        DB::statement('ALTER TABLE unit_signal MODIFY ID BIGINT UNSIGNED NOT NULL');

        // 2) احذف الـ primary key
        DB::statement('ALTER TABLE unit_signal DROP PRIMARY KEY');

        // 3) ضيف index عادي (مش unique) للبحث السريع
        Schema::table('unit_signal', function (Blueprint $table) {
            $table->index('ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unit_signal', function (Blueprint $table) {
            $table->dropIndex(['ID']);
        });

        // هيفشل لو فيه ID مكرر في البيانات
        DB::statement('ALTER TABLE unit_signal ADD PRIMARY KEY (ID)');
        DB::statement('ALTER TABLE unit_signal MODIFY ID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
    
    }
};
