<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * جدول مصادر التكليف (السيد المحافظ / النائب / السكرتير العام / إلخ)
 * لو عندكم جدول مشابه بالفعل، احذف هذه الـ migration واربط الكود
 * بالجدول الموجود (غيّر task_source_id في migration التكليفات).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // بيانات مبدئية مطابقة لقائمة الإكسل الحالية
        DB::table('task_sources')->insert([
            ['name' => 'السيد المحافظ', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'السيد نائب المحافظ', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'السكرتير العام', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مركز ومدينة بيلا', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مديرية الإسكان', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مركز ومدينة كفر الشيخ', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('task_sources');
    }
};
