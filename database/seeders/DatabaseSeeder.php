<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
  public function run(): void
{
    $this->call([
        RolesAndPermissionsSeeder::class,
        EvaluationPermissionSeeder::class,

    ]);

    // اختياري: تعمل أول Admin مباشرة
    $admin = User::factory()->create([
        'name' => 'Admin',
        'email' => 'admin@admin.com',
        'password' => bcrypt('password'), // غيّرها بعدين طبعاً
    ]);
    $admin->assignRole('مشرف عام');
}
}
