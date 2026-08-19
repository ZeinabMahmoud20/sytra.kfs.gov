@extends('layouts.app')

@section('title', 'إدارة المستخدمين')
@section('page-title', 'إدارة المستخدمين')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-black text-primary">الموظفون</h1>
        <a href="{{ route('admin.users.create') }}"
            class="bg-accent hover:bg-accent-hover text-white font-bold px-5 py-3 rounded-xl flex items-center gap-2">
            <i class="fas fa-user-plus"></i> إضافة موظف جديد
        </a>
    </div>

    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl font-bold">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead class="bg-slate-50 text-slate-500 text-sm">
                    <tr>
                        <th class="px-6 py-4 font-bold">الاسم</th>
                        <th class="px-6 py-4 font-bold">البريد الإلكتروني</th>
                        <th class="px-6 py-4 font-bold">رقم الهاتف</th>
                        <th class="px-6 py-4 font-bold">الأدوار</th>
                        <th class="px-6 py-4 font-bold text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($users as $user)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-bold text-primary">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ $user->email }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ $user->phone_number ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @foreach ($user->roles as $role)
                                    <span class="px-2 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-bold">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100">
                                        <i class="fas fa-edit text-sm"></i>
                                    </a>
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                            onsubmit="return confirm('متأكد من حذف هذا المستخدم؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100">
                                                <i class="fas fa-trash text-sm"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $users->links() }}</div>
@endsection
