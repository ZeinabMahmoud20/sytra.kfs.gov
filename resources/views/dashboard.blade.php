@extends('layouts.app')

@section('title', 'لوحة التحكم - الشبكة الوطنية للطوارئ')
@section('page-title', 'لوحة التحكم')

@section('content')
<div class="mb-10 text-right">
    <h1 class="text-3xl font-extrabold text-primary mb-2">مرحباً، {{ auth()->user()->name }}</h1>
    <p class="text-slate-500 text-lg">إليك ملخص سريع لأهم الإحصائيات والبلاغات الجارية اليوم.</p>
</div>

{{-- بطاقات الإحصائيات - أرقام تجريبية لحد ما نربطها بالداتا الحقيقية --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
    <div
        class="bg-white p-6 rounded-3xl shadow-sm border-2 border-slate-100 hover:border-accent hover:shadow-none transition-all cursor-pointer flex items-center gap-5 stat-card">
        <div
            class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-3xl shadow-sm">
            <i class="fas fa-file-alt"></i>
        </div>
        <div>
            <p class="text-slate-400 font-bold mb-1">إجمالي البلاغات</p>
            <h4 class="text-2xl font-black text-primary">{{ $totalReports ?? 0 }}</h4>
        </div>
    </div>
    <div
        class="bg-white p-6 rounded-3xl shadow-sm border-2 border-slate-100 hover:border-accent hover:shadow-none transition-all cursor-pointer flex items-center gap-5 stat-card">
        <div
            class="w-16 h-16 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center text-3xl shadow-sm">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div>
            <p class="text-slate-400 font-bold mb-1">بلاغات جارية</p>
            <h4 class="text-2xl font-black text-primary">{{ $activeIncidents ?? 0 }}</h4>
        </div>
    </div>
    <div
        class="bg-white p-6 rounded-3xl shadow-sm border-2 border-slate-100 hover:border-accent hover:shadow-none transition-all cursor-pointer flex items-center gap-5 stat-card">
        <div
            class="w-16 h-16 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center text-3xl shadow-sm">
            <i class="fas fa-broadcast-tower"></i>
        </div>
        <div>
            <p class="text-slate-400 font-bold mb-1">إشارات اليوم</p>
            <h4 class="text-2xl font-black text-primary">{{ $todaySignals ?? 0 }}</h4>
        </div>
    </div>

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
                        'تم استلام البلاغ' => 'bg-red-100 text-red-600',
                        'قيد المعالجة' => 'bg-yellow-100 text-yellow-700',
                        'تم التنفيذ', 'تم الانتهاء' => 'bg-green-100 text-green-700',
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

    <div class="space-y-6">
        <h3 class="text-2xl font-black text-primary"><i class="fas fa-bolt ml-2 text-accent"></i>إجراءات سريعة
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="{{ Route::has('reports.create') ? route('reports.create') : '#' }}"
                class="flex items-center justify-between p-6 bg-primary text-white rounded-3xl border-2 border-transparent hover:border-accent hover:shadow-none transition-all group">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center text-2xl group-hover:bg-accent group-hover:text-white transition-all">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div>
                        <p class="font-black text-lg">إضافة بلاغ جديد</p>
                        <p class="text-slate-300 text-sm">تسجيل حالة طارئة بالمحافظة</p>
                    </div>
                </div>
                <i class="fas fa-chevron-left"></i>
            </a>
            <a href="{{ Route::has('signals.create') ? route('signals.create') : '#' }}"
                class="flex items-center justify-between p-6 bg-white rounded-3xl border-2 border-slate-100 hover:border-accent hover:shadow-none transition-all group">
                <div class="flex items-center gap-4 text-primary">
                    <div
                        class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-2xl group-hover:bg-accent group-hover:text-white transition-all">
                        <i class="fas fa-microphone"></i>
                    </div>
                    <div>
                        <p class="font-black text-lg">تسجيل إشارة</p>
                        <p class="text-slate-400 text-sm">إرسال إشارة للجهات المختصة</p>
                    </div>
                </div>
                <i class="fas fa-chevron-left text-slate-300"></i>
            </a>
            <a href="{{ Route::has('reports.index') ? route('reports.index') : '#' }}"
                class="flex items-center justify-between p-6 bg-white rounded-3xl border-2 border-slate-100 hover:border-accent hover:shadow-none transition-all group">
                <div class="flex items-center gap-4 text-primary">
                    <div
                        class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-2xl group-hover:bg-accent group-hover:text-white transition-all">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <p class="font-black text-lg">عرض التقارير</p>
                        <p class="text-slate-400 text-sm">الإحصائيات والرسوم البيانية</p>
                    </div>
                </div>
                <i class="fas fa-chevron-left text-slate-300"></i>
            </a>
        </div>
    </div>
</div>
@endsection