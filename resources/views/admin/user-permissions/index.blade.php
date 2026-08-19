@extends('layouts.app')

@section('title', 'الصلاحيات المباشرة للموظفين')
@section('page-title', 'الصلاحيات المباشرة')

@section('content')
    <h1 class="text-2xl font-black text-primary mb-6">إدارة الصلاحيات المباشرة لكل موظف</h1>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead class="bg-slate-50 text-slate-500 text-sm">
                    <tr>
                        <th class="px-6 py-4 font-bold">الاسم</th>
                        <th class="px-6 py-4 font-bold">الأدوار</th>
                        <th class="px-6 py-4 font-bold">صلاحيات مباشرة</th>
                        <th class="px-6 py-4 font-bold text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($users as $user)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 font-bold text-primary">{{ $user->name }}</td>
                            <td class="px-6 py-4">
                                @foreach ($user->roles as $role)
                                    <span class="px-2 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-bold">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td class="px-6 py-4">
                                @if ($user->permissions->isNotEmpty())
                                    <span class="px-2 py-1 bg-purple-50 text-purple-600 rounded-full text-xs font-bold">
                                        {{ $user->permissions->count() }} صلاحية إضافية
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">لا يوجد</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('admin.user-permissions.edit', $user) }}"
                                    class="inline-flex items-center gap-2 bg-purple-50 text-purple-600 hover:bg-purple-100 font-bold px-4 py-2 rounded-lg text-sm">
                                    <i class="fas fa-key"></i> إدارة الصلاحيات
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $users->links() }}</div>
@endsection
