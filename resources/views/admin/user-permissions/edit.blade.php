@extends('layouts.app')

@section('title', 'صلاحيات: ' . $targetUser->name)
@section('page-title', 'إدارة الصلاحيات المباشرة')

@section('content')
    <div class="max-w-4xl mx-auto w-full space-y-6">

        <div class="bg-primary text-white rounded-3xl p-6">
            <h2 class="text-2xl font-black">{{ $targetUser->name }}</h2>
            <p class="text-slate-300 text-sm">
                الأدوار الحالية:
                @forelse ($targetUser->roles as $role)
                    <span class="px-2 py-1 bg-white/10 rounded-full text-xs font-bold ml-1">{{ $role->name }}</span>
                @empty
                    <span class="text-slate-400">لا يوجد دور</span>
                @endforelse
            </p>
        </div>

        @if (session('success'))
            <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl font-bold">{{ session('success') }}</div>
        @endif

        {{-- فورم إضافة الصلاحيات المباشرة --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h3 class="font-black text-primary mb-2">صلاحيات إضافية مباشرة</h3>
            <p class="text-slate-400 text-sm mb-4">
                دي صلاحيات زيادة عن اللي بييجي من دور الموظف. علّم الصلاحيات اللي عايز تديها له بشكل مباشر.
            </p>

            <form method="POST" action="{{ route('admin.user-permissions.update', $targetUser) }}" class="space-y-6">
                @csrf
                @method('PUT')

                @include('admin.roles._permissions-checklist', ['checkedPermissions' => old('permissions', $directPermissions)])

                <div class="pt-4 border-t border-slate-100">
                    <button type="submit" class="bg-primary text-white font-black px-8 py-3 rounded-xl">
                        حفظ الصلاحيات المباشرة
                    </button>
                </div>
            </form>
        </div>

        {{-- الصلاحيات الفعلية المجمعة --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h3 class="font-black text-primary mb-4">
                <i class="fas fa-list-check text-accent ml-2"></i>
                الصلاحيات الفعلية الكاملة ({{ $effectivePermissions->count() }})
            </h3>
            <p class="text-slate-400 text-sm mb-4">دي كل الصلاحيات اللي الموظف عنده فعلياً دلوقتي، سواء جاية من دوره أو مُضافة له مباشرة.</p>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-bold">الصلاحية</th>
                            <th class="px-4 py-3 font-bold">المصدر</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($effectivePermissions as $permission)
                            <tr>
                                <td class="px-4 py-3 font-bold text-slate-700">{{ $permission['name'] }}</td>
                                <td class="px-4 py-3">
                                    @if ($permission['source'] === 'مباشرة')
                                        <span class="px-2 py-1 bg-purple-50 text-purple-600 rounded-full text-xs font-bold">مباشرة</span>
                                    @else
                                        <span class="px-2 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-bold">دور: {{ $permission['source'] }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-4 py-6 text-center text-slate-400">مفيش صلاحيات خالص لهذا الموظف</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <a href="{{ route('admin.user-permissions.index') }}" class="block text-center bg-slate-100 text-slate-700 font-bold py-3 rounded-xl">
            رجوع للقائمة
        </a>
    </div>
@endsection
