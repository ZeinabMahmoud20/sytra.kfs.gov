<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * يضيف نظام الأدوار والتسلسل الإداري لجدول المستخدمين الموجود بالفعل في تطبيقكم.
 *
 * الأدوار المدعومة (enum 'role'):
 *   admin              - صلاحية كاملة على النظام كله
 *   director           - السيد المحافظ / النائب (اطلاع كامل + تقارير)
 *   manager            - مدير يشوف تكليفات فريقه (المرؤوسين) فقط
 *   assignment_manager - مسؤول التكليفات - يضيف/يعدّل/يوزّع كل التكليفات من كل المراكز
 *   supervisor         - مشرف مركز/جهة - يشوف تكليفات جهته
 *   staff              - موظف تنفيذي - يشوف ويحدّث تكليفاته هو بس
 *
 * manager_id: تسلسل هرمي ذاتي (self-reference) على نفس جدول users
 * عشان المدير يقدر يشوف تكليفات مرؤوسيه تلقائياً.
 *
 * ⚠️ إذا كان جدول users عندكم فيه بالفعل عمود role أو مايشابهه، عدّل أسماء
 * الأعمدة هنا قبل التشغيل لتفادي التعارض.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role')) {
                $table->enum('role', [
                    'admin',
                    'director',
                    'manager',
                    'assignment_manager',
                    'supervisor',
                    'staff',
                ])->default('staff')->after('email');
            }

            if (! Schema::hasColumn('users', 'manager_id')) {
                $table->foreignId('manager_id')
                    ->nullable()
                    ->after('role')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'entity_id')) {
                // ربط المستخدم بالجهة/المركز اللي بيتبعله (اختياري - يفيد في فلترة التكليفات)
                $table->unsignedBigInteger('entity_id')->nullable()->after('manager_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'manager_id')) {
                $table->dropConstrainedForeignId('manager_id');
            }
            if (Schema::hasColumn('users', 'entity_id')) {
                $table->dropColumn('entity_id');
            }
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
