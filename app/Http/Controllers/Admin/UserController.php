<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->orderBy('name')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create', [
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'employee_full_name' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'employee_full_name' => $validated['employee_full_name'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
        ]);

        $user->syncRoles($validated['roles']);

        return redirect()->route('admin.users.index')->with('success', "تم إنشاء حساب \"{$user->name}\" بنجاح");
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'targetUser' => $user,
            'roles' => Role::orderBy('name')->get(),
            'userRoles' => $user->roles->pluck('name')->toArray(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'employee_full_name' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,name'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'employee_full_name' => $validated['employee_full_name'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            ...(!empty($validated['password']) ? ['password' => Hash::make($validated['password'])] : []),
        ]);

        $user->syncRoles($validated['roles']);

        return redirect()->route('admin.users.index')->with('success', "تم تحديث بيانات \"{$user->name}\" بنجاح");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'مينفعش تحذف حسابك الشخصي وانت مسجل دخول بيه.');
        }

        $user->delete();

        return back()->with('success', 'تم حذف المستخدم بنجاح');
    }
}