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
     * صلاحيات أساسية بنستخدمها في فحص الأدوار/المستخدمين،
     * بنتأكد إنها موجودة برضه قبل الفحص عشان مفيش استثناء PermissionDoesNotExist.
     */
    private const BASELINE_PERMISSIONS = [
        'reports.view',
        'signals.view',
        'tmam.view',
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

        // 1) إنشاء الصلاحيات الجديدة + كل صلاحيات الدور لو مش موجودة
        //    (مهم جدًا على السيرفر: ممكن dashboard.view تكون مش موجودة أصلًا
        //     ووقتها syncPermissions بيرمي PermissionDoesNotExist)
        $permissionsToEnsure = array_unique(array_merge(
            self::NEW_PERMISSIONS,
            self::BASELINE_PERMISSIONS,
            self::REPORTS_VIEWER_PERMISSIONS,
        ));

        foreach ($permissionsToEnsure as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // 2) الحفاظ على وصول باقي الأدوار زي ما كان قبل التقسيم:
        //    أي دور شايف البلاغات أو الإشارات كان بيشوف الإعدادات كمان.
        //    وكمان بندّي كل الأدوار القديمة dashboard.view عشان الصفحة الرئيسية
        //    ما تتقفلش عليهم بعد ما بقى عليها middleware.
        foreach (Role::with('permissions')->get() as $role) {
            if ($role->name === self::REPORTS_VIEWER_ROLE) {
                continue; // الدور ده ليه صلاحيات محددة تحت، ومش لازم يشوف الإعدادات
            }

            $role->givePermissionTo('dashboard.view');

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

        // 3) نفس الحفاظ على الوصول للمستخدمين:
        //    اللي واخد صلاحيات مباشرة بندّيله صلاحيات الإعدادات/التمام/التكليف المناسبة،
        //    وأي مستخدم مش واخد dashboard.view بأي طريقة بندّيهاله عشان مايتقفلش عليه.
        //    مستخدمين دور "مشاهد التقارير" بنستثنيهم من المنطق ده تمامًا
        //    عشان ماياخدوش صلاحيات مباشرة تكسر تقييد الدور.
        foreach (User::with('permissions')->get() as $user) {
            if ($user->hasRole(self::REPORTS_VIEWER_ROLE)) {
                continue;
            }

            if (! $user->hasPermissionTo('dashboard.view')) {
                $user->givePermissionTo('dashboard.view');
            }

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
