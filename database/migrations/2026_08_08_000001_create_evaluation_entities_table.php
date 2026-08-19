<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول الجهات الخاصة بموديول التقييم (مستقل تمامًا عن جدول entities بتاع مشروع البلاغات)
     */
    public function up(): void
    {
        Schema::create('evaluation_entities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->unique(); // كود اختياري للجهة
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes(); // عشان لو اتحذفت جهة متأثرش على تقييمات شهور سابقة
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_entities');
    }
};
