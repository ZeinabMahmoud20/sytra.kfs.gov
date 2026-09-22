@extends('layouts.app')

@section('title', 'أنواع البلاغات - الإعدادات')
@section('page-title', 'أنواع البلاغات وجهات البلاغ')

@section('content')
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <h1 class="text-2xl font-black text-primary">أنواع البلاغات وجهات البلاغ</h1>
        @can('reports.create')
            <a href="{{ route('settings.reporting-types.create') }}"
                class="bg-accent hover:bg-accent-hover text-white font-bold px-5 py-3 rounded-xl transition-all flex items-center gap-2">
                <i class="fas fa-plus"></i> إضافة نوع بلاغ جديد
            </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm font-bold">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead class="bg-slate-50 text-slate-500 text-sm">
                    <tr>
                        <th class="px-4 py-4 font-bold">#</th>
                        <th class="px-4 py-4 font-bold">نوع البلاغ</th>
                        <th class="px-4 py-4 font-bold">جهة البلاغ</th>
                        <th class="px-4 py-4 font-bold text-center">إنترنت</th>
                        <th class="px-4 py-4 font-bold text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($reportingTypes as $reportingType)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-4 text-slate-400">{{ $reportingType->REPORT_ID }}</td>
                            <td class="px-4 py-4 font-bold text-primary">{{ $reportingType->REPORT_SORT ?: '-' }}</td>
                            <td class="px-4 py-4 font-bold text-primary">{{ $reportingType->AUTHORITY ?: '-' }}</td>
                            <td class="px-4 py-4 text-center">
                                @if ($reportingType->IS_INTERNET)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                                        <i class="fas fa-globe"></i> إنترنت
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                                        <i class="fas fa-building"></i> داخلي
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @can('reports.edit')
                                        <a href="{{ route('settings.reporting-types.edit', $reportingType) }}" title="تعديل"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100">
                                            <i class="fas fa-edit text-sm"></i>
                                        </a>
                                    @endcan
                                    @can('reports.delete')
                                        <form method="POST" action="{{ route('settings.reporting-types.destroy', $reportingType) }}"
                                            onsubmit="return confirm('هل أنت متأكد من حذف نوع البلاغ؟')">
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
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">لا توجد أنواع بلاغات مسجلة</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $reportingTypes->links() }}</div>
@endsection