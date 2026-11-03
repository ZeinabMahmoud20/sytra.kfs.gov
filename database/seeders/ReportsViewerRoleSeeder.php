<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ReportsViewerRoleSeeder extends Seeder
{
    /**
     * اسم دور "مشاهد التقارير" المستخدم في النظام
     */
    private const REPORTS_VIEWER_ROLE = 'مشاهد التقارير';

    /**
     * الصلاحيات الجديدة اللي بيتعملها إنشاء لو مش موجودة
     */
    private const NEW_PERMISSIONS = [
        'settings.view',     // شاشات الإعدادات (أنواع البلاغات، جهات الإشارة... إلخ)
        'tasks.sources',     // مصادر التكليف
        'tasks.entities',    // جهات التعامل
        'tmam.statistics',   // إحصائيات الجهات فقط
    ];

    /**
     * الصلاحيات النهائية لدور "مشاهد التقارير"
     */
    private const REPORTS_VIEWER_PERMISSIONS = [
        'dashboard.view',
        'reports.view',
        'reports.monthly-stats',
        'signals.view',
        'tmam.statistics',
        'tasks.view',
    ];

    /**
     * php artisan db:seed --class=ReportsViewerRoleSeeder
     *
     * Seeder آمن ومتكرر (idempotent): تقدر تشغّله أكتر من مرة من غير ما يضاعف حاجة.
     * بيستخدم على السيرفر عشان يضيف الصلاحيات الجديدة من غير ما يشغّل DatabaseSeeder
     * (لأن DatabaseSeeder بيعمل مستخدم Admin جديد كل مرة).
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1) إنشاء الصلاحيات الجديدة لو مش موجودة
        foreach (self::NEW_PERMISSIONS as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // 2) الحفاظ على وصول باقي الأدوار زي ما كان قبل التقسيم:
        //    أي دور شايف البلاغات أو الإشارات كان بيشوف الإعدادات كمان.
        foreach (Role::with('permissions')->get() as $role) {
            if ($role->name === self::REPORTS_VIEWER_ROLE) {
                continue; // الدور ده ليه صلاحيات محددة تحت، ومش لازم يشوف الإعدادات
            }

            if ($role->hasAnyPermission(['reports.view', 'signals.view'])) {
                $role->givePermissionTo('settings.view');
            }

            if ($role->hasPermissionTo('tmam.view')) {
                $role->givePermissionTo('tmam.statistics');
            }

            if ($role->hasPermissionTo('tasks.view')) {
                $role->givePermissionTo(['tasks.sources', 'tasks.entities']);
            }
        }

        // 3) نفس الحفاظ على الوصول للمستخدمين اللي واخدين صلاحيات مباشرة (من غير دور)
        foreach (User::with('permissions')->get() as $user) {
            $directPermissions = $user->getDirectPermissions()->pluck('name');

            if ($directPermissions->intersect(['reports.view', 'signals.view'])->isNotEmpty()) {
                $user->givePermissionTo('settings.view');
            }

            if ($directPermissions->contains('tmam.view')) {
                $user->givePermissionTo('tmam.statistics');
            }

            if ($directPermissions->contains('tasks.view')) {
                $user->givePermissionTo(['tasks.sources', 'tasks.entities']);
            }
        }

        // 4) ضبط صلاحيات دور "مشاهد التقارير" على القائمة النهائية بالظبط
        $reportsViewer = Role::firstOrCreate([
            'name' => self::REPORTS_VIEWER_ROLE,
            'guard_name' => 'web',
        ]);

        $reportsViewer->syncPermissions(self::REPORTS_VIEWER_PERMISSIONS);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command?->info(
            'تم ضبط الصلاحيات. دور "' . self::REPORTS_VIEWER_ROLE . '": '
            . implode(', ', self::REPORTS_VIEWER_PERMISSIONS)
        );
    }
}
