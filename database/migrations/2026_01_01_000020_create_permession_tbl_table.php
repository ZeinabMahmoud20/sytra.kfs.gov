<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PERMESSION_TBL', function (Blueprint $table) {
            // ملاحظة: الجدول الأصلي في SQL Server مفيهوش Primary Key معرّف.
            // بنحافظ على نفس التصميم هنا، وننصح بإضافة PK لاحقاً لو أمكن.
            $table->string('PER_NAME', 500)->nullable();
            $table->unsignedBigInteger('USER_ID')->nullable();
            // $timestamps معطلة: الجدول الأصلي معندوش created_at/updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PERMESSION_TBL');
    }
};