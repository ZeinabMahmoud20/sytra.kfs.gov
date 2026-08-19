@extends('layouts.app')

@section('title', 'إدارة الأدوار')
@section('page-title', 'إدارة الأدوار')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-black text-primary">الأدوار الوظيفية</h1>
        <a href="{{ route('admin.roles.create') }}"
            class="bg-accent hover:bg-accent-hover text-white font-bold px-5 py-3 rounded-xl flex items-center gap-2">
            <i class="fas fa-plus"></i> إضافة دور جديد
        </a>
    </div>

    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl font-bold">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($roles as $role)
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-black text-primary">{{ $role->name }}</h3>
                    <span class="text-xs bg-slate-100 text-slate-500 px-3 py-1 rounded-full font-bold">
                        {{ $role->users_count }} موظف
                    </span>
                </div>
                <p class="text-slate-400 text-sm">{{ $role->permissions_count }} صلاحية مرتبطة</p>
                <div class="flex gap-2 pt-2 border-t border-slate-100">
                    <a href="{{ route('admin.roles.edit', $role) }}"
                        class="flex-1 text-center bg-amber-50 text-amber-600 hover:bg-amber-100 font-bold py-2 rounded-lg text-sm">
                        <i class="fas fa-edit"></i> تعديل
                    </a>
                    <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="flex-1"
                        onsubmit="return confirm('متأكد إنك عايز تحذف الدور ده؟')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-50 text-red-600 hover:bg-red-100 font-bold py-2 rounded-lg text-sm">
                            <i class="fas fa-trash"></i> حذف
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endsection
