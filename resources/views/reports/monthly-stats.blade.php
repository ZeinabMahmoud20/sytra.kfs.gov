@extends('layouts.app')

@section('title', 'إحصائيات البلاغات الشهرية - الشبكة الوطنية للطوارئ')
@section('page-title', 'إحصائيات البلاغات الشهرية')

@section('content')
<div class="space-y-8">

    {{-- فلتر الشهر والسنة --}}
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
        <form method="GET" action="{{ route('reports.monthly-stats') }}"
            class="flex flex-col md:flex-row md:items-end gap-4">
            <div>
                <label class="block text-slate-400 font-bold mb-2 text-sm">الشهر</label>
                <select name="month"
                    class="w-full md:w-48 rounded-2xl border-slate-200 focus:border-accent focus:ring-accent">
                    @foreach ($months as $m => $name)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-slate-400 font-bold mb-2 text-sm">السنة</label>
                <select name="year"
                    class="w-full md:w-40 rounded-2xl border-slate-200 focus:border-accent focus:ring-accent">
                    @foreach ($years as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit"
                    class="px-6 py-3 rounded-2xl bg-accent text-white font-bold hover:bg-accent-hover transition-all">
                    <i class="fas fa-filter ml-2"></i>عرض النتائج
                </button>
                <a href="{{ route('reports.monthly-stats') }}"
                    class="px-5 py-3 rounded-2xl border-2 border-slate-200 text-slate-500 font-bold hover:border-accent hover:text-accent transition-all">
                    <i class="fas fa-undo ml-1"></i>إعادة
                </a>
            </div>
        </form>
    </div>

    {{-- عنوان الشهر المعروض --}}
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
        <h3 class="text-2xl font-black text-primary"><i class="fas fa-calendar-alt ml-2 text-accent"></i>إحصائيات شهر {{ $monthName }} {{ $year }}</h3>
        <p class="text-slate-500 font-bold mt-1">ملخص أداء المراكز والمدن في البلاغات خلال الشهر</p>
    </div>

    {{-- بطاقات الملخص --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        @php
        $cards = [
            ['label' => 'إجمالي البلاغات', 'icon' => 'fa-layer-group', 'cls' => 'bg-blue-50 text-blue-600', 'value' => $totals['total']],
            ['label' => 'تم التنفيذ', 'icon' => 'fa-check-circle', 'cls' => 'bg-green-50 text-green-600', 'value' => $totals['executed']],
            ['label' => 'قيد المعالجة', 'icon' => 'fa-spinner', 'cls' => 'bg-yellow-50 text-yellow-600', 'value' => $totals['processing']],
            ['label' => 'تم الانتهاء', 'icon' => 'fa-flag-checkered', 'cls' => 'bg-purple-50 text-purple-600', 'value' => $totals['finished']],
            ['label' => 'تم استلام البلاغ', 'icon' => 'fa-inbox', 'cls' => 'bg-slate-100 text-slate-600', 'value' => $totals['received']],
        ];
        @endphp
        @foreach ($cards as $card)
        <div
            class="bg-white p-5 rounded-3xl shadow-sm border-2 border-slate-100 transition-all flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl flex-shrink-0 {{ $card['cls'] }} flex items-center justify-center text-2xl shadow-sm">
                <i class="fas {{ $card['icon'] }}"></i>
            </div>
            <div class="min-w-0">
                <p class="text-slate-400 font-bold mb-1 text-sm">{{ $card['label'] }}</p>
                <h4 class="text-2xl font-black text-primary">{{ $card['value'] }}</h4>
            </div>
        </div>
        @endforeach
    </div>

    {{-- جدول إحصائيات المراكز والمدن --}}
    <div>
        <h3 class="text-2xl font-black text-primary mb-5"><i class="fas fa-building ml-2 text-accent"></i>إحصائيات المراكز والمدن (مرتبة تنازلياً حسب أعلى إنجاز)</h3>
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right">
                    <thead class="bg-slate-50 text-slate-500 text-sm">
                        <tr>
                            <th class="px-5 py-4 font-bold">#</th>
                            <th class="px-5 py-4 font-bold">المركز / المدينة</th>
                            <th class="px-5 py-4 font-bold text-center">إجمالي البلاغات</th>
                            <th class="px-5 py-4 font-bold text-center">تم استلام البلاغ</th>
                            <th class="px-5 py-4 font-bold text-center">قيد المعالجة</th>
                            <th class="px-5 py-4 font-bold text-center">تم التنفيذ</th>
                            <th class="px-5 py-4 font-bold text-center">تم الانتهاء</th>
                            <th class="px-5 py-4 font-bold text-center">نسبة التنفيذ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($rowStats as $index => $city)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-4 font-black text-accent">{{ $loop->iteration }}</td>
                            <td class="px-5 py-4 font-bold text-primary">{{ $city->CITY_NAME }}</td>
                            <td class="px-5 py-4 text-center font-black text-primary">{{ $city->total_count }}</td>
                            <td class="px-5 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-700">{{ $city->received_count }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-sm font-bold bg-yellow-100 text-yellow-700">{{ $city->processing_count }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-700">{{ $city->executed_count }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-sm font-bold bg-purple-100 text-purple-700">{{ $city->finished_count }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="font-black text-primary">{{ $city->completion_rate }}%</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-slate-400">
                                لا توجد بلاغات في الشهر المحدد
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- الرسوم البيانية --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
            <h3 class="text-xl font-black text-primary mb-4"><i class="fas fa-chart-bar ml-2 text-accent"></i>بلاغات المراكز والمدن حسب الحالة</h3>
            <div class="relative h-80"><canvas id="citiesChart"></canvas></div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
            <h3 class="text-xl font-black text-primary mb-4"><i class="fas fa-chart-pie ml-2 text-accent"></i>توزيع البلاغات حسب الحالة</h3>
            <div class="relative h-80"><canvas id="statusChart"></canvas></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Chart === 'undefined') return;

        Chart.defaults.font.family = "'Cairo', sans-serif";
        Chart.defaults.color = '#64748b';

        const colors = {
            received: '#3b82f6',
            processing: '#eab308',
            executed: '#22c55e',
            finished: '#8b5cf6',
        };

        // رسم بياني أعمدة مكدسة لبلاغات المراكز والمدن حسب الحالة
        const citiesCtx = document.getElementById('citiesChart');
        if (citiesCtx) {
            const cities = @json($chartCities);
            new Chart(citiesCtx, {
                type: 'bar',
                data: {
                    labels: cities.map(c => c.name),
                    datasets: [
                        { label: 'تم استلام البلاغ', data: cities.map(c => c.received), backgroundColor: colors.received },
                        { label: 'قيد المعالجة', data: cities.map(c => c.processing), backgroundColor: colors.processing },
                        { label: 'تم التنفيذ', data: cities.map(c => c.executed), backgroundColor: colors.executed },
                        { label: 'تم الانتهاء', data: cities.map(c => c.finished), backgroundColor: colors.finished },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { font: { family: 'Cairo' } } }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            ticks: { font: { family: 'Cairo' }, maxRotation: 45 }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    }
                }
            });
        }

        // رسم بياني دائري لتوزيع الحالة
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            const totals = @json($chartTotals);
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($statuses),
                    datasets: [{
                        data: [
                            totals['executed'],
                            totals['processing'],
                            totals['finished'],
                            totals['received'],
                        ],
                        backgroundColor: [colors.executed, colors.processing, colors.finished, colors.received],
                        borderWidth: 2,
                        borderColor: '#fff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { font: { family: 'Cairo' } } }
                    }
                }
            });
        }
    });
</script>
@endpush