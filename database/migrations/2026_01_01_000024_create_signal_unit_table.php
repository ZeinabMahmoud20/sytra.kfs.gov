<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('SIGNAL_UNIT', function (Blueprint $table) {
            // ملاحظة: الجدول الأصلي في SQL Server مفيهوش Primary Key معرّف.
            // بنحافظ على نفس التصميم هنا، وننصح بإضافة PK لاحقاً لو أمكن.
            $table->unsignedBigInteger('ID');
            $table->date('UNIT_SIGNAL_DATE')->nullable();
            $table->time('UNIT_SIGNAL_TIME')->nullable();
            $table->text('UNIT_SIGNAL_SUBJECT')->nullable();
            $table->text('UNIT_SIGNAL_CONTENT')->nullable();
            $table->unsignedBigInteger('UNIT_AUTHORITY_ID')->nullable();
            $table->unsignedBigInteger('MAIN_SEND_ID')->nullable();
            $table->string('MAIN_SEND_CODE', 50)->nullable();
            $table->string('UNIT_SIGNAL_TYPE', 50)->nullable();
            // $timestamps معطلة: الجدول الأصلي معندوش created_at/updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('SIGNAL_UNIT');
    }
};