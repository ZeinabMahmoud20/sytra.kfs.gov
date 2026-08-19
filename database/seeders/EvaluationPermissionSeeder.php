<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EvaluationPermissionSeeder extends Seeder
{
    /**
     * الاسم زي ما هو معرّف في RolesAndPermissionsSeeder بتاعك بالظبط
     */
    private const SUPER_ADMIN_ROLE = 'مشرف عام';

    /**
     * php artisan db:seed --class=EvaluationPermissionSeeder
     *
     * بعد كده هتدي الصلاحيات للاتنين المسئولين عن التقييم بس، مثلاً:
     * $user->givePermissionTo(['evaluate-entities', 'view-evaluation-dashboard']);
     */
    public function run(): void
    {
        $permissions = [
            Permission::firstOrCreate(['name' => 'evaluate-entities']),         // تسجيل تقييم يومي
            Permission::firstOrCreate(['name' => 'manage-evaluation-entities']), // CRUD الجهات
            Permission::firstOrCreate(['name' => 'view-evaluation-dashboard']),  // مشاهدة الداشبورد
        ];

        // ديها تلقائيًا لـ role "مشرف عام" لو موجود، بدل ما تدخلي على كل مستخدم لوحده
        $superAdminRole = Role::where('name', self::SUPER_ADMIN_ROLE)->first();

        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permissions);
        }
    }
}
