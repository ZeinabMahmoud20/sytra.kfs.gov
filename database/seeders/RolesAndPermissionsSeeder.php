<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // نصفّي الكاش القديم بتاع الصلاحيات قبل ما نبدأ
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // -------------------------------------------------------------
        // الصلاحيات (Permissions) - مقسّمة حسب كل موديول
        // -------------------------------------------------------------
$permissions = [
    // الصفحة الرئيسية
    'dashboard.view',

    // موديول البلاغات
    'reports.view',
    'reports.create',
    'reports.edit',
    'reports.delete',
    'reports.lock',
    'reports.monthly-stats',

    // موديول الإشارات
    'signals.view',
    'signals.create',
    'signals.edit',
    'signals.delete',

    // موديول تمام
    'tmam.view',
    'tmam.statistics', // إحصائيات الجهات فقط (من غير شاشات التمام)
    'tmam.create',
    'tmam.edit',
    'tmam.delete',
    'tmam.import',   // <-- جديد: رفع الجهات من Excel

    'tasks.view',
    'tasks.sources',  // صفحات مصادر التكليف
    'tasks.entities', // صفحات جهات التعامل
    'tasks.create',
    'tasks.edit',
    'tasks.delete',

    // موديول دليل الاتصال
    'contact-guides.view',
    'contact-guides.create',
    'contact-guides.edit',
    'contact-guides.delete',
    'contact-guides.import',

    // الإعدادات (أنواع البلاغات وجهات البلاغ والإشارة والإخطار ومضمون الإشارة)
    'settings.view',

    // إدارة النظام
    'users.manage',
    'roles.manage',
    'system_records.view',
];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // -------------------------------------------------------------
        // الأدوار (Roles) وربطها بالصلاحيات المناسبة
        // -------------------------------------------------------------

        // 1. مشرف عام - كل الصلاحيات
        $admin = Role::firstOrCreate(['name' => 'مشرف عام']);
        $admin->syncPermissions(Permission::all());

        // 2. موظف استقبال بلاغات
        $reportsEmployee = Role::firstOrCreate(['name' => 'موظف استقبال بلاغات']);
        $reportsEmployee->syncPermissions([
            'dashboard.view',
            'reports.view',
            'reports.create',
            'reports.edit',
            'settings.view',
        ]);

        // 3. موظف إشارات
        $signalsEmployee = Role::firstOrCreate(['name' => 'موظف إشارات']);
        $signalsEmployee->syncPermissions([
            'dashboard.view',
            'signals.view',
            'signals.create',
            'signals.edit',
            'settings.view',
        ]);

        // 4. موظف تمام
        $tmamEmployee = Role::firstOrCreate(['name' => 'موظف تمام']);
        $tmamEmployee->syncPermissions([
            'dashboard.view',
            'tmam.view',
            'tmam.statistics',
            'tmam.create',
            'tmam.edit',
        ]);

        // 5. مستخدم عادي - مشاهدة فقط على كل الموديولات
        $viewer = Role::firstOrCreate(['name' => 'مستخدم عادي']);
        $viewer->syncPermissions([
            'dashboard.view',
            'reports.view',
            'signals.view',
            'tmam.view',
            'tmam.statistics',
            'settings.view',
        ]);

        // 6. مشاهد التقارير - الصفحة الرئيسية وصفحات التقارير بس (بدون إضافة/تعديل/حذف)
        $reportsViewer = Role::firstOrCreate(['name' => 'مشاهد التقارير']);
        $reportsViewer->syncPermissions([
            'dashboard.view',       // الصفحة الرئيسية
            'reports.view',         // تقارير البلاغات + لوحة تحكم البلاغات
            'reports.monthly-stats',// إحصائيات البلاغات الشهرية
            'signals.view',         // قائمة/تقارير الإشارات
            'tmam.statistics',      // إحصائيات الجهات فقط (بدون شاشات التمام)
            'tasks.view',           // قائمة/لوحة متابعة التكليفات
            // ملحوظة: مفيش settings.view → مش هيشوف الإعدادات
            // ملحوظة: مفيش tasks.sources / tasks.entities → مش هيشوف مصادر التكليف أو جهات التعامل
        ]);
    }
}