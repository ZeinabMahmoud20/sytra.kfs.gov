@extends('layouts.app')

@section('title', 'لوحة التحكم - الشبكة الوطنية للطوارئ')
@section('page-title', 'لوحة التحكم')

@section('content')
<div class="mb-10 text-right">
    <h1 class="text-3xl font-extrabold text-primary mb-2">مرحباً، {{ auth()->user()->name }}</h1>
    <p class="text-slate-500 text-lg">إليك ملخص سريع لأهم الإحصائيات والبلاغات الجارية اليوم.</p>
</div>

{{-- بطاقات الإحصائيات: كل بطاقة بتفتح شاشة التقارير الخاصة بها --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    @can('reports.view')
    <a href="{{ route('reports.index') }}"
        class="group bg-white p-6 rounded-3xl shadow-sm border-2 border-slate-100 hover:border-accent hover:shadow-md transition-all cursor-pointer flex items-center gap-5 stat-card">
        <div
            class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-3xl shadow-sm flex-shrink-0">
            <i class="fas fa-file-alt"></i>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-slate-400 font-bold mb-1">إجمالي البلاغات</p>
            <h4 class="text-2xl font-black text-primary">{{ $totalReports ?? 0 }}</h4>
            <p class="text-slate-400 text-xs font-bold mt-1">منها {{ $activeIncidents ?? 0 }} بلاغ جارٍ</p>
        </div>
        <i class="fas fa-chevron-left text-slate-300 group-hover:text-accent group-hover:-translate-x-1 transition-all"></i>
    </a>
    @endcan

    @can('signals.view')
    <a href="{{ route('signals.index') }}"
        class="group bg-white p-6 rounded-3xl shadow-sm border-2 border-slate-100 hover:border-accent hover:shadow-md transition-all cursor-pointer flex items-center gap-5 stat-card">
        <div
            class="w-16 h-16 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center text-3xl shadow-sm flex-shrink-0">
            <i class="fas fa-broadcast-tower"></i>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-slate-400 font-bold mb-1">إجمالي الإشارات</p>
            <h4 class="text-2xl font-black text-primary">{{ $totalSignals ?? 0 }}</h4>
            <p class="text-slate-400 text-xs font-bold mt-1">منها {{ $todaySignals ?? 0 }} إشارة اليوم</p>
        </div>
        <i class="fas fa-chevron-left text-slate-300 group-hover:text-accent group-hover:-translate-x-1 transition-all"></i>
    </a>
    @endcan

    @canany(['tmam.view', 'tmam.statistics'])
    <a href="{{ auth()->user()->can('tmam.view') ? route('daily-attendances.index') : route('entity-attendance-dashboard') }}"
        class="group bg-white p-6 rounded-3xl shadow-sm border-2 border-slate-100 hover:border-accent hover:shadow-md transition-all cursor-pointer flex items-center gap-5 stat-card">
        <div
            class="w-16 h-16 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center text-3xl shadow-sm flex-shrink-0">
            <i class="fas fa-clipboard-check"></i>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-slate-400 font-bold mb-1">إجمالي التمامات</p>
            <h4 class="text-2xl font-black text-primary">{{ $totalTmams ?? 0 }}</h4>
            <p class="text-slate-400 text-xs font-bold mt-1">منها {{ $todayTmams ?? 0 }} تمام اليوم</p>
        </div>
        <i class="fas fa-chevron-left text-slate-300 group-hover:text-accent group-hover:-translate-x-1 transition-all"></i>
    </a>
    @endcanany

    @can('tasks.view')
    <a href="{{ route('tasks.index') }}"
        class="group bg-white p-6 rounded-3xl shadow-sm border-2 border-slate-100 hover:border-accent hover:shadow-md transition-all cursor-pointer flex items-center gap-5 stat-card">
        <div
            class="w-16 h-16 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center text-3xl shadow-sm flex-shrink-0">
            <i class="fas fa-tasks"></i>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-slate-400 font-bold mb-1">إجمالي التكليفات</p>
            <h4 class="text-2xl font-black text-primary">{{ $totalTasks ?? 0 }}</h4>
            <p class="text-slate-400 text-xs font-bold mt-1">منها {{ $activeTasks ?? 0 }} تكليف جارٍ</p>
        </div>
        <i class="fas fa-chevron-left text-slate-300 group-hover:text-accent group-hover:-translate-x-1 transition-all"></i>
    </a>
    @endcan
</div>

<div class="flex flex-col gap-10">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h3 class="text-2xl font-black text-primary"><i class="fas fa-history ml-2 text-accent"></i>أحدث
                البلاغات</h3>
            @if (Route::has('reports.index'))
            <a href="{{ route('reports.index') }}" class="text-accent font-bold hover:underline">عرض الكل</a>
            @endif
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right">
                    <thead class="bg-slate-50 text-slate-500 text-sm">
                        <tr>
                            <th class="px-6 py-4 font-bold">رقم البلاغ</th>
                            <th class="px-6 py-4 font-bold">نوع البلاغ</th>
                            <th class="px-6 py-4 font-bold">الموقع</th>
                            <th class="px-6 py-4 font-bold">الوقت</th>
                            <th class="px-6 py-4 font-bold text-center">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">

                        @forelse ($recentReports ?? [] as $report)
                        @php
                        $statusClasses = match ($report->REQUEST_STATUS) {
                        'تم الاستلام' => 'bg-red-100 text-red-600',
                        'قيد المعالجة' => 'bg-yellow-100 text-yellow-700',
                        'تم التنفيذ' => 'bg-green-100 text-green-700',
                        'تم الانتهاء' => 'bg-blue-100 text-blue-700',
                        default => 'bg-slate-100 text-slate-600',
                        };
                        @endphp
                        <tr class="hover:bg-slate-100 transition-colors">
                            <td class="px-6 py-4 font-bold text-primary">#{{ $report->ID }}</td>
                            <td class="px-6 py-4 font-semibold">{{ $report->reportingType->REPORT_SORT ?? '-' }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ $report->PLACE_Accident }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ $report->REPORT_START_TIME }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-sm font-black {{ $statusClasses }}">
                                    {{ $report->REQUEST_STATUS }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">لا توجد بلاغات حتى الآن</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
