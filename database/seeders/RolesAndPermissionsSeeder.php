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
    // موديول البلاغات
    'reports.view',
    'reports.create',
    'reports.edit',
    'reports.delete',
    'reports.lock',

    // موديول الإشارات
    'signals.view',
    'signals.create',
    'signals.edit',
    'signals.delete',

    // موديول تمام
    'tmam.view',
    'tmam.create',
    'tmam.edit',
    'tmam.delete',
    'tmam.import',   // <-- جديد: رفع الجهات من Excel

        'tasks.view',
    'tasks.create',
    'tasks.edit',
    'tasks.delete',

    // موديول دليل الاتصال
    'contact-guides.view',
    'contact-guides.create',
    'contact-guides.edit',
    'contact-guides.delete',
    'contact-guides.import',

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
            'reports.view',
            'reports.create',
            'reports.edit',
        ]);

        // 3. موظف إشارات
        $signalsEmployee = Role::firstOrCreate(['name' => 'موظف إشارات']);
        $signalsEmployee->syncPermissions([
            'signals.view',
            'signals.create',
            'signals.edit',
        ]);

        // 4. موظف تمام
        $tmamEmployee = Role::firstOrCreate(['name' => 'موظف تمام']);
        $tmamEmployee->syncPermissions([
            'tmam.view',
            'tmam.create',
            'tmam.edit',
        ]);

        // 5. مستخدم عادي - مشاهدة فقط على كل الموديولات
        $viewer = Role::firstOrCreate(['name' => 'مستخدم عادي']);
        $viewer->syncPermissions([
            'reports.view',
            'signals.view',
            'tmam.view',
        ]);

        
    }
}