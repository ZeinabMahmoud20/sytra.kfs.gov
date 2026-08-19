@extends('layouts.app')

@section('title', 'إحصائيات الجهات - نظام التمامات')
@section('page-title', 'إحصائيات الجهات')

@section('content')
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <h1 class="text-2xl font-black text-primary">إحصائيات الجهات</h1>
    </div>

    {{-- فلتر التاريخ --}}
    <form method="GET" action="{{ route('entity-attendance-dashboard') }}"
        class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mb-8">
        <div class="flex items-end gap-4 flex-wrap">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-sm font-bold text-slate-500 mb-2">من تاريخ</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}"
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent transition">
            </div>
            <div class="flex-1 min-w-[180px]">
                <label class="block text-sm font-bold text-slate-500 mb-2">إلى تاريخ</label>
                <input type="date" name="date_to" value="{{ $dateTo }}"
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent transition">
            </div>
            <button type="submit"
                class="bg-accent hover:bg-accent/90 text-white font-bold px-6 py-2.5 rounded-xl text-sm transition-all flex items-center gap-2">
                <i class="fas fa-filter"></i> تصفية
            </button>
            <a href="{{ route('entity-attendance-dashboard') }}"
                class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold px-6 py-2.5 rounded-xl text-sm transition-all flex items-center gap-2">
                <i class="fas fa-undo"></i> إعادة ضبط
            </a>
        </div>
        <div class="flex items-center gap-2 mt-4 flex-wrap">
            <span class="text-xs text-slate-400 font-bold">سريع:</span>
            <a href="{{ route('entity-attendance-dashboard', ['date_from' => now()->toDateString(), 'date_to' => now()->toDateString()]) }}"
                class="px-3 py-1 rounded-lg text-xs font-bold bg-slate-50 text-slate-500 hover:bg-accent hover:text-white transition-all">اليوم</a>
            <a href="{{ route('entity-attendance-dashboard', ['date_from' => now()->subDays(6)->toDateString(), 'date_to' => now()->toDateString()]) }}"
                class="px-3 py-1 rounded-lg text-xs font-bold bg-slate-50 text-slate-500 hover:bg-accent hover:text-white transition-all">آخر 7 أيام</a>
            <a href="{{ route('entity-attendance-dashboard', ['date_from' => now()->subDays(29)->toDateString(), 'date_to' => now()->toDateString()]) }}"
                class="px-3 py-1 rounded-lg text-xs font-bold bg-slate-50 text-slate-500 hover:bg-accent hover:text-white transition-all">آخر 30 يوم</a>
            <a href="{{ route('entity-attendance-dashboard', ['date_from' => now()->subDays(89)->toDateString(), 'date_to' => now()->toDateString()]) }}"
                class="px-3 py-1 rounded-lg text-xs font-bold bg-slate-50 text-slate-500 hover:bg-accent hover:text-white transition-all">آخر 90 يوم</a>
            <a href="{{ route('entity-attendance-dashboard', ['date_from' => now()->startOfYear()->toDateString(), 'date_to' => now()->toDateString()]) }}"
                class="px-3 py-1 rounded-lg text-xs font-bold bg-slate-50 text-slate-500 hover:bg-accent hover:text-white transition-all">من أول السنة</a>
        </div>
    </form>

    {{-- بطاقات الإحصائيات --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-5">
            <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl">
                <i class="fas fa-building"></i>
            </div>
            <div>
                <p class="text-slate-400 font-bold text-sm">إجمالي الجهات</p>
                <h4 class="text-2xl font-black text-primary">{{ $entityStats->total() }}</h4>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-5">
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <div>
                <p class="text-slate-400 font-bold text-sm">إجمالي مرات التم</p>
                <h4 class="text-2xl font-black text-primary">{{ $totalAppearances }}</h4>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-5">
            <div class="w-14 h-14 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center text-2xl">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <p class="text-slate-400 font-bold text-sm">مرات الأداء (تم)</p>
                <h4 class="text-2xl font-black text-green-600">{{ $totalDone }}</h4>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-5">
            <div class="w-14 h-14 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center text-2xl">
                <i class="fas fa-times-circle"></i>
            </div>
            <div>
                <p class="text-slate-400 font-bold text-sm">مرات عدم الأداء (لم يتم)</p>
                <h4 class="text-2xl font-black text-red-600">{{ $totalNotDone }}</h4>
            </div>
        </div>
    </div>

    {{-- نسبة الأداء --}}
    @if ($totalAppearances > 0)
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mb-8">
        <div class="flex items-center justify-between mb-3">
            <p class="font-bold text-primary">نسبة الأداء الإجمالية</p>
            <span class="font-black text-lg text-accent">{{ $doneRate }}%</span>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-3">
            <div class="bg-accent h-3 rounded-full transition-all duration-500" style="width: {{ $doneRate }}%"></div>
        </div>
    </div>
    @endif

    {{-- أكثر 5 جهات أداءً + أكثر 5 جهات عدم أداء --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-green-50/50">
                <h3 class="font-black text-green-700 text-lg"><i class="fas fa-trophy ml-2"></i>أكثر 5 جهات أداءً</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($top5Done as $i => $stat)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center font-black text-sm">{{ $i + 1 }}</span>
                            <span class="font-bold text-primary">{{ $stat['entity']->name }}</span>
                        </div>
                        <span class="font-black text-green-600 bg-green-50 px-3 py-1 rounded-full text-sm">{{ $stat['done'] }} مرة</span>
                    </div>
                @empty
                    <div class="px-6 py-6 text-center text-slate-400 text-sm">لا توجد بيانات</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-red-50/50">
                <h3 class="font-black text-red-700 text-lg"><i class="fas fa-exclamation-triangle ml-2"></i>أكثر 5 جهات عدم أداء</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($top5NotDone as $i => $stat)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-black text-sm">{{ $i + 1 }}</span>
                            <span class="font-bold text-primary">{{ $stat['entity']->name }}</span>
                        </div>
                        <span class="font-black text-red-600 bg-red-50 px-3 py-1 rounded-full text-sm">{{ $stat['not_done'] }} مرة</span>
                    </div>
                @empty
                    <div class="px-6 py-6 text-center text-slate-400 text-sm">لا توجد بيانات</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- الجدول الرئيسي --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-black text-primary text-lg"><i class="fas fa-table ml-2 text-accent"></i>كل الجهات</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead class="bg-slate-50 text-slate-500 text-sm">
                    <tr>
                        <th class="px-6 py-4 font-bold">#</th>
                        <th class="px-6 py-4 font-bold">اسم الجهة</th>
                        <th class="px-6 py-4 font-bold text-center">عدد مرات التم</th>
                        <th class="px-6 py-4 font-bold text-center">تم (أداء)</th>
                        <th class="px-6 py-4 font-bold text-center">لم يتم</th>
                        <th class="px-6 py-4 font-bold text-center">نسبة الأداء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($entityStats as $stat)
                        @php
                            $rate = $stat['total'] > 0 ? round(($stat['done'] / $stat['total']) * 100, 1) : 0;
                            $rateColor = $rate >= 80 ? 'text-green-600' : ($rate >= 50 ? 'text-amber-600' : 'text-red-600');
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 text-slate-400 text-sm">{{ ($entityStats->currentPage() - 1) * $entityStats->perPage() + $loop->iteration }}</td>
                            <td class="px-6 py-4 font-bold text-primary">{{ $stat['entity']->name }}</td>
                            <td class="px-6 py-4 text-center font-semibold text-slate-600">{{ $stat['total'] }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1 font-bold text-green-600">
                                    <i class="fas fa-check text-xs"></i> {{ $stat['done'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1 font-bold text-red-600">
                                    <i class="fas fa-times text-xs"></i> {{ $stat['not_done'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-black {{ $rateColor }}">{{ $rate }}%</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">لا توجد بيانات في الفترة المحددة</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($entityStats->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $entityStats->links() }}
        </div>
        @endif
    </div>
@endsection
