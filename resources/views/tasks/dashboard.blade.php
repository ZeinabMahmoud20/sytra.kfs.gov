@extends('layouts.app')

@section('title', 'لوحة متابعة التكليفات - الشبكة الوطنية للطوارئ')
@section('page-title', 'متابعة التكليفات')

@section('content')
<div class="space-y-8">

    {{-- فلتر التاريخ بين تاريخين --}}
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
        <form method="GET" action="{{ route('tasks.dashboard') }}"
            class="flex flex-col md:flex-row md:items-end gap-4">
            <div class="flex-1">
                <label class="block text-slate-400 font-bold mb-2 text-sm">من تاريخ</label>
                <input type="date" name="from" value="{{ $from }}"
                    class="w-full rounded-2xl border-slate-200 focus:border-accent focus:ring-accent">
            </div>
            <div class="flex-1">
                <label class="block text-slate-400 font-bold mb-2 text-sm">إلى تاريخ</label>
                <input type="date" name="to" value="{{ $to }}"
                    class="w-full rounded-2xl border-slate-200 focus:border-accent focus:ring-accent">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit"
                    class="px-6 py-3 rounded-2xl bg-accent text-white font-bold hover:bg-accent-hover transition-all">
                    <i class="fas fa-filter ml-2"></i>عرض النتائج
                </button>
                @if ($from || $to)
                <a href="{{ route('tasks.dashboard') }}"
                    class="px-5 py-3 rounded-2xl border-2 border-slate-200 text-slate-500 font-bold hover:border-accent hover:text-accent transition-all">
                    <i class="fas fa-undo ml-1"></i>إعادة
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- أولاً: مؤشرات التنفيذ --}}
    <div>
        <h3 class="text-2xl font-black text-primary mb-5"><i class="fas fa-tasks ml-2 text-accent"></i>مؤشرات التنفيذ</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            @php
            $cards = [
                ['label' => 'إجمالي التكليفات', 'icon' => 'fa-layer-group', 'cls' => 'bg-blue-50 text-blue-600', 'value' => $executionIndicators['total']],
                ['label' => 'تم التنفيذ', 'icon' => 'fa-check-circle', 'cls' => 'bg-green-50 text-green-600', 'value' => $executionIndicators['completed']],
                ['label' => 'جاري التنفيذ', 'icon' => 'fa-spinner', 'cls' => 'bg-yellow-50 text-yellow-600', 'value' => $executionIndicators['in_progress']],
                ['label' => 'متأخر', 'icon' => 'fa-exclamation-triangle', 'cls' => 'bg-orange-50 text-orange-600', 'value' => $executionIndicators['overdue']],
                ['label' => 'لم يبدأ', 'icon' => 'fa-hourglass-start', 'cls' => 'bg-red-50 text-red-600', 'value' => $executionIndicators['not_started']],
                ['label' => 'متوقف', 'icon' => 'fa-pause-circle', 'cls' => 'bg-slate-100 text-slate-600', 'value' => $executionIndicators['halted']],
            ];
            @endphp
            @foreach ($cards as $card)
            <div
                class="bg-white p-5 rounded-3xl shadow-sm border-2 border-slate-100 hover:border-accent hover:shadow-none transition-all flex items-center gap-4">
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
    </div>

    {{-- ثانياً: مؤشرات الزمن --}}
    <div>
        <h3 class="text-2xl font-black text-primary mb-5"><i class="fas fa-clock ml-2 text-accent"></i>مؤشرات الزمن</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-3xl shadow-sm border-2 border-slate-100 hover:border-accent transition-all">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-3xl shadow-sm">
                        <i class="fas fa-stopwatch"></i>
                    </div>
                    <div>
                        <p class="text-slate-400 font-bold mb-1">متوسط زمن الاستجابة (أيام)</p>
                        <h4 class="text-2xl font-black text-primary">{{ $avgResponse }}</h4>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border-2 border-slate-100 hover:border-accent transition-all">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center text-3xl shadow-sm">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div>
                        <p class="text-slate-400 font-bold mb-1">متوسط زمن التنفيذ الفعلي (أيام تأخير)</p>
                        <h4 class="text-2xl font-black text-primary">{{ $avgDelay }}</h4>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border-2 border-slate-100 hover:border-accent transition-all">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center text-3xl shadow-sm">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <p class="text-slate-400 font-bold mb-1">نسبة الالتزام بالمواعيد</p>
                        <h4 class="text-2xl font-black text-primary">{{ $commitmentRate }}%</h4>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border-2 border-slate-100 hover:border-accent transition-all">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-3xl shadow-sm">
                        <i class="fas fa-percent"></i>
                    </div>
                    <div>
                        <p class="text-slate-400 font-bold mb-1">متوسط نسبة الإنجاز العامة</p>
                        <h4 class="text-2xl font-black text-primary">{{ $avgCompletion }}%</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- الرسوم البيانية --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
            <h3 class="text-xl font-black text-primary mb-4"><i class="fas fa-chart-pie ml-2 text-accent"></i>توزيع التكليفات حسب الحالة</h3>
            <div class="relative h-72"><canvas id="statusChart"></canvas></div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
            <h3 class="text-xl font-black text-primary mb-4"><i class="fas fa-chart-bar ml-2 text-accent"></i>تكليفات الجهات حسب الحالة</h3>
            <div class="relative h-72"><canvas id="entityChart"></canvas></div>
        </div>
    </div>

    {{-- ثالثاً: جدول مؤشرات الجهات --}}
    <div>
        <h3 class="text-2xl font-black text-primary mb-5"><i class="fas fa-building ml-2 text-accent"></i>مؤشرات الجهات</h3>
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right">
                    <thead class="bg-slate-50 text-slate-500 text-sm">
                        <tr>
                            <th class="px-5 py-4 font-bold">الجهة</th>
                            <th class="px-5 py-4 font-bold text-center">إجمالي التكليفات</th>
                            <th class="px-5 py-4 font-bold text-center">تم التنفيذ</th>
                            <th class="px-5 py-4 font-bold text-center">جاري التنفيذ</th>
                            <th class="px-5 py-4 font-bold text-center">متأخر</th>
                            <th class="px-5 py-4 font-bold text-center">لم يبدأ</th>
                            <th class="px-5 py-4 font-bold text-center">متوقف</th>
                            <th class="px-5 py-4 font-bold text-center">نسبة التنفيذ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($entities as $entity)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-4 font-bold text-primary">{{ $entity->name }}</td>
                            <td class="px-5 py-4 text-center font-bold">{{ $entity->total_tasks }}</td>
                            <td class="px-5 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-700">{{ $entity->completed_tasks }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-sm font-bold bg-yellow-100 text-yellow-700">{{ $entity->in_progress_tasks }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-sm font-bold bg-orange-100 text-orange-700">{{ $entity->overdue_tasks }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-sm font-bold bg-red-100 text-red-700">{{ $entity->not_started_tasks }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-sm font-bold bg-slate-100 text-slate-600">{{ $entity->halted_tasks }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="font-black text-primary">{{ $entity->completion_rate }}%</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-slate-400">لا توجد تكليفات في الفترة المحددة</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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

        // رسم بياني دائري لتوزيع الحالة
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($statuses),
                    datasets: [{
                        data: [
                            {{ $executionIndicators['completed'] }},
                            {{ $executionIndicators['in_progress'] }},
                            {{ $executionIndicators['overdue'] }},
                            {{ $executionIndicators['not_started'] }},
                            {{ $executionIndicators['halted'] }},
                        ],
                        backgroundColor: ['#22c55e', '#eab308', '#f97316', '#ef4444', '#64748b'],
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

        // رسم بياني أعمدة لتكليفات الجهات
        const entityCtx = document.getElementById('entityChart');
        if (entityCtx) {
            const entities = @json($chartEntities);
            new Chart(entityCtx, {
                type: 'bar',
                data: {
                    labels: entities.map(e => e.name),
                    datasets: [
                        { label: 'تم التنفيذ', data: entities.map(e => e.completed), backgroundColor: '#22c55e' },
                        { label: 'جاري التنفيذ', data: entities.map(e => e.in_progress), backgroundColor: '#eab308' },
                        { label: 'متأخر', data: entities.map(e => e.overdue), backgroundColor: '#f97316' },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { font: { family: 'Cairo' } } }
                    },
                    scales: {
                        x: { ticks: { font: { family: 'Cairo' }, maxRotation: 45 } }
                    }
                }
            });
        }
    });
</script>
@endpush
