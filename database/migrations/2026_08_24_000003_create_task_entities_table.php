<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * جدول مستقل خاص بالجهات/المراكز المختصة بالتعامل مع التكليفات فقط.
 *
 * ⚠️ ملحوظة مهمة: عندكم بالفعل جدول/موديل Entity في نظام الحضور والانصراف
 * (name, main_location) - ده جدول مختلف تماماً وما ليهوش أي علاقة بيه، عشان
 * محدش يتأثر بتعديلات موديول التكليفات. لو حبيتوا لاحقاً تربطوا الاتنين
 * (مثلاً لو "الجهة" في الحضور هي نفسها "الجهة" في التكليفات)، قولولي وهعمل
 * علاقة بينهم بدل الجدول المنفصل ده.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_entities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['مركز', 'مدينة', 'إدارة', 'مديرية', 'أخرى'])->default('أخرى');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('task_entities')->insert([
            ['name' => 'مركز سيطرة الشبكة الوطنية', 'type' => 'مركز', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مركز ومدينة كفر الشيخ', 'type' => 'مركز', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مركز ومدينة دسوق', 'type' => 'مركز', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مركز ومدينة فوه', 'type' => 'مركز', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مركز ومدينة مطوبس', 'type' => 'مركز', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مركز ومدينة سيدي سالم', 'type' => 'مركز', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مركز ومدينة بلطيم', 'type' => 'مركز', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مركز ومدينة الحامول', 'type' => 'مركز', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مركز ومدينة بيلا', 'type' => 'مركز', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مركز ومدينة الرياض', 'type' => 'مركز', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مركز ومدينة قلين', 'type' => 'مركز', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مدينة سيدي غازي', 'type' => 'مدينة', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مدينة برج البرلس', 'type' => 'مدينة', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مدينة مصيف بلطيم', 'type' => 'مدينة', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'إدارات ديوان عام المحافظة', 'type' => 'إدارة', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('task_entities');
    }
};
