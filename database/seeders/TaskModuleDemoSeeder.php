<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder اختياري للتجربة فقط - بيدّي أول مستخدم دور admin، وبيوزّع
 * باقي المستخدمين أدوار متنوعة عشان تقدروا تجربوا نظام الصلاحيات.
 * لا تشغّله على بيانات إنتاج حقيقية بدون مراجعة.
 */
class TaskModuleDemoSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::orderBy('id')->get();

        if ($users->isEmpty()) {
            $this->command->warn('لا يوجد مستخدمين - أضف مستخدمين أولاً.');
            return;
        }

        $roles = ['admin', 'assignment_manager', 'manager', 'supervisor', 'staff', 'staff', 'staff'];

        foreach ($users as $i => $user) {
            $user->update([
                'role' => $roles[$i % count($roles)],
            ]);
        }

        $this->command->info('تم توزيع الأدوار التجريبية على ' . $users->count() . ' مستخدم.');
    }
}
