<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class UserPermissionController extends Controller
{
    public function index()
    {
        $users = User::with('roles', 'permissions')->orderBy('name')->paginate(20);

        return view('admin.user-permissions.index', compact('users'));
    }

    public function edit(User $user)
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });

        // الصلاحيات المباشرة بس (مش الجاية من الدور)
        $directPermissions = $user->getDirectPermissions()->pluck('name')->toArray();

        // الصلاحيات الفعلية (دور + مباشر) مع تحديد مصدر كل واحدة
        $effectivePermissions = $user->getAllPermissions()->map(function ($permission) use ($user, $directPermissions) {
            if (in_array($permission->name, $directPermissions)) {
                $source = 'مباشرة';
            } else {
                $rolesGrantingIt = $user->roles->filter(fn ($role) => $role->hasPermissionTo($permission))->pluck('name');
                $source = $rolesGrantingIt->implode('، ');
            }

            return ['name' => $permission->name, 'source' => $source];
        })->sortBy('name');

        return view('admin.user-permissions.edit', [
            'targetUser' => $user,
            'permissions' => $permissions,
            'directPermissions' => $directPermissions,
            'effectivePermissions' => $effectivePermissions,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        // syncPermissions هنا بتأثر بس على الصلاحيات المباشرة (model_has_permissions)
        // ومش بتلمس صلاحيات الدور خالص - آمنة 100%
        $user->syncPermissions($validated['permissions'] ?? []);

        return redirect()
            ->route('admin.user-permissions.edit', $user)
            ->with('success', "تم تحديث الصلاحيات المباشرة لـ \"{$user->name}\" بنجاح");
    }
}