<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        // بنجمّع الصلاحيات حسب الجزء اللي قبل النقطة (reports.view -> reports)
        // عشان تتعرض منظمة في الواجهة بدل قايمة طويلة عشوائية
        $permissions = Permission::orderBy('name')->get()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });

        return view('admin.permissions.index', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:permissions,name', 'regex:/^[a-z_-]+\.[a-z_-]+$/'],
        ], [
            'name.regex' => 'صيغة الصلاحية لازم تكون بالشكل: module.action مثل reports.approve (حروف صغيرة، underscore، أو شرطة، ومفصولة بنقطة).',
        ]);

        $permission = Permission::create(['name' => $validated['name'], 'guard_name' => 'web']);

        // أي صلاحية جديدة تتضاف تلقائياً لدور "مشرف عام" عشان يفضل عنده كل الصلاحيات دايماً
        $adminRole = \Spatie\Permission\Models\Role::where('name', 'مشرف عام')->first();
        $adminRole?->givePermissionTo($permission);

        return back()->with('success', "تم إنشاء الصلاحية \"{$validated['name']}\" بنجاح");
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return back()->with('success', 'تم حذف الصلاحية بنجاح');
    }
}