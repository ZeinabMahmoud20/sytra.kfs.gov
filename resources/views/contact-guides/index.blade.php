@extends('layouts.app')

@section('title', 'دليل الاتصال')
@section('page-title', 'دليل الاتصال')

@section('content')
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <h1 class="text-2xl font-black text-primary">دليل الاتصال</h1>
        <div class="flex items-center gap-3 flex-wrap">
            @can('contact-guides.import')
                <a href="{{ route('contact-guides.import.form') }}"
                    class="bg-white border-2 border-primary text-primary hover:bg-primary hover:text-white font-bold px-5 py-3 rounded-xl transition-all flex items-center gap-2">
                    <i class="fas fa-file-excel"></i> رفع من Excel
                </a>
            @endcan
            @can('contact-guides.create')
                <a href="{{ route('contact-guides.create') }}"
                    class="bg-accent hover:bg-accent-hover text-white font-bold px-5 py-3 rounded-xl transition-all flex items-center gap-2">
                    <i class="fas fa-plus"></i> إضافة جهة اتصال
                </a>
            @endcan
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm font-bold">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-700 text-sm font-bold">
            <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
        </div>
    @endif

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <form method="GET" action="{{ route('contact-guides.index') }}"
            class="p-4 border-b border-slate-100 flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-52 space-y-1">
                <label class="block text-xs font-black text-slate-600">بحث بالاسم</label>
                <div class="relative">
                    <i class="fas fa-search absolute top-1/2 -translate-y-1/2 left-4 text-slate-400 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="ابحث باسم الإدارة أو مدير الإدارة"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none pr-9">
                </div>
            </div>

            <div class="w-64 min-w-48 space-y-1">
                <label class="block text-xs font-black text-slate-600">فلترة حسب الإدارة</label>
                <select name="department"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                    <option value="">كل الإدارات</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department }}" {{ request('department') == $department ? 'selected' : '' }}>
                            {{ $department }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                class="bg-primary text-white font-bold px-6 py-2.5 rounded-xl hover:bg-accent transition-all">
                <i class="fas fa-filter"></i> بحث
            </button>

            @if (request('search') || request('department'))
                <a href="{{ route('contact-guides.index') }}"
                    class="bg-slate-100 text-slate-600 font-bold px-6 py-2.5 rounded-xl hover:bg-slate-200 transition-all">
                    <i class="fas fa-times"></i> مسح
                </a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead class="bg-slate-50 text-slate-500 text-sm">
                    <tr>
                        <th class="px-4 py-4 font-bold">#</th>
                        <th class="px-4 py-4 font-bold">اسم الإدارة</th>
                        <th class="px-4 py-4 font-bold">مدير الإدارة</th>
                        <th class="px-4 py-4 font-bold">رقم الهاتف</th>
                        <th class="px-4 py-4 font-bold">رقم الأرضي</th>
                        <th class="px-4 py-4 font-bold">هاتف إضافي</th>
                        <th class="px-4 py-4 font-bold text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($guides as $guide)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-4 text-slate-400">{{ $guide->id }}</td>
                            <td class="px-4 py-4 font-bold text-primary">{{ $guide->department_name }}</td>
                            <td class="px-4 py-4" dir="auto">{{ $guide->manager_name ?? '-' }}</td>
                            <td class="px-4 py-4" dir="ltr">{{ $guide->phone_number ?? '-' }}</td>
                            <td class="px-4 py-4" dir="ltr">{{ $guide->landline_number ?? '-' }}</td>
                            <td class="px-4 py-4" dir="ltr">{{ $guide->additional_phone ?? '-' }}</td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @can('contact-guides.edit')
                                        <a href="{{ route('contact-guides.edit', $guide) }}" title="تعديل"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100">
                                            <i class="fas fa-edit text-sm"></i>
                                        </a>
                                    @endcan
                                    @can('contact-guides.delete')
                                        <form method="POST" action="{{ route('contact-guides.destroy', $guide) }}"
                                            onsubmit="return confirm('هل أنت متأكد من حذف سجل دليل الاتصال؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="حذف"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100">
                                                <i class="fas fa-trash text-sm"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-400">لا توجد بيانات في دليل الاتصال</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $guides->links() }}</div>
@endsection