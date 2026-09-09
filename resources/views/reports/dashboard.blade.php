@extends('layouts.app')

@section('title', 'لوحة تحكم البلاغات - الشبكة الوطنية للطوارئ')
@section('page-title', 'لوحة تحكم البلاغات')

@section('content')
<div class="space-y-8">

    {{-- الفلاتر --}}
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
        <form method="GET" action="{{ route('reports.dashboard') }}"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
            <input type="hidden" name="village_center" value="{{ $villageCenter }}">
            <div>
                <label class="block text-slate-400 font-bold mb-2 text-sm">من تاريخ</label>
                <input type="date" name="from" value="{{ $from }}"
                    class="w-full rounded-2xl border-slate-200 focus:border-accent focus:ring-accent">
            </div>
            <div>
                <label class="block text-slate-400 font-bold mb-2 text-sm">إلى تاريخ</label>
                <input type="date" name="to" value="{{ $to }}"
                    class="w-full rounded-2xl border-slate-200 focus:border-accent focus:ring-accent">
            </div>
            <div>
                <label class="block text-slate-400 font-bold mb-2 text-sm">المدينة / المركز</label>
                <select name="city" class="w-full rounded-2xl border-slate-200 focus:border-accent focus:ring-accent">
                    <option value="">الكل</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city->CITY_ID }}" {{ $cityId == $city->CITY_ID ? 'selected' : '' }}>{{ $city->CITY_NAME }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-slate-400 font-bold mb-2 text-sm">نوع البلاغ</label>
                <select name="type" class="w-full rounded-2xl border-slate-200 focus:border-accent focus:ring-accent">
                    <option value="">الكل</option>
                    @foreach ($reportTypes as $type)
                        <option value="{{ $type->REPORT_ID }}" {{ $typeId == $type->REPORT_ID ? 'selected' : '' }}>{{ $type->REPORT_SORT }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-slate-400 font-bold mb-2 text-sm">الحالة</label>
                <select name="status" class="w-full rounded-2xl border-slate-200 focus:border-accent focus:ring-accent">
                    <option value="">الكل</option>
                    <option value="تم استلام البلاغ" {{ $status == 'تم استلام البلاغ' ? 'selected' : '' }}>تم استلام البلاغ</option>
                    <option value="قيد المعالجة" {{ $status == 'قيد المعالجة' ? 'selected' : '' }}>قيد المعالجة</option>
                    <option value="تم التنفيذ" {{ $status == 'تم التنفيذ' ? 'selected' : '' }}>تم التنفيذ</option>
                    <option value="تم الانتهاء" {{ $status == 'تم الانتهاء' ? 'selected' : '' }}>تم الانتهاء</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit"
                    class="px-5 py-3 rounded-2xl bg-accent text-white font-bold hover:bg-accent-hover transition-all">
                    <i class="fas fa-filter ml-1"></i>عرض
                </button>
                <a href="{{ route('reports.dashboard') }}"
                    class="px-4 py-3 rounded-2xl border-2 border-slate-200 text-slate-500 font-bold hover:border-accent hover:text-accent transition-all">
                    <i class="fas fa-undo ml-1"></i>إعادة
                </a>
            </div>
        </form>
    </div>

    {{-- بطاقات KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        @php
        $cards = [
            ['label' => 'إجمالي البلاغات (الفترة)', 'icon' => 'fa-layer-group', 'cls' => 'bg-blue-50 text-blue-600', 'value' => $totalReports],
            ['label' => 'بلاغات اليوم', 'icon' => 'fa-calendar-day', 'cls' => 'bg-emerald-50 text-emerald-600', 'value' => $todayReports],
            ['label' => 'بلاغات هذا الأسبوع', 'icon' => 'fa-calendar-week', 'cls' => 'bg-indigo-50 text-indigo-600', 'value' => $weekReports],
            ['label' => 'نسبة الإنجاز', 'icon' => 'fa-percent', 'cls' => 'bg-green-50 text-green-600', 'value' => $completionRate . '%'],
            ['label' => 'الضحايا', 'icon' => 'fa-user-slash', 'cls' => 'bg-red-50 text-red-600', 'value' => $totalDeceased],
        ];
        @endphp
        @foreach ($cards as $card)
        <div class="bg-white p-5 rounded-3xl shadow-sm border-2 border-slate-100 transition-all flex items-center gap-4">
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

    {{-- الرسم البياني الزمني --}}
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
        <h3 class="text-xl font-black text-primary mb-4"><i class="fas fa-chart-line ml-2 text-accent"></i>اتجاه البلاغات اليومي (آخر 30 يوم)</h3>
        <div class="relative h-64"><canvas id="dailyChart"></canvas></div>
    </div>

    {{-- تحليل جغرافي + أنواع البلاغات --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
            <h3 class="text-xl font-black text-primary mb-4"><i class="fas fa-map-marker-alt ml-2 text-accent"></i>أكثر المراكز تكراراً للبلاغات</h3>
            <div class="relative h-72"><canvas id="citiesChart"></canvas></div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
            <h3 class="text-xl font-black text-primary mb-4"><i class="fas fa-chart-pie ml-2 text-accent"></i>توزيع البلاغات حسب النوع</h3>
            <div class="relative h-72"><canvas id="typesChart"></canvas></div>
        </div>
    </div>

    {{-- جدول أداء المراكز --}}
    <div>
        <h3 class="text-2xl font-black text-primary mb-5"><i class="fas fa-trophy ml-2 text-accent"></i>أداء المراكز والمدن (مرتبة تنازلياً حسب نسبة الإنجاز)</h3>
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
                            <th class="px-5 py-4 font-bold text-center">نسبة الإنجاز</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($cityPerformance as $city)
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
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-16 bg-slate-100 rounded-full h-2 overflow-hidden">
                                        <div class="h-full bg-accent rounded-full" style="width: {{ $city->completion_rate }}%"></div>
                                    </div>
                                    <span class="font-black text-primary">{{ $city->completion_rate }}%</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-slate-400">لا توجد بيانات في الفترة المحددة</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- البلاغات حسب القرية داخل المركز (مع dropdown) --}}
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
            <div>
                <h3 class="text-2xl font-black text-primary"><i class="fas fa-map-pin ml-2 text-accent"></i>البلاغات في كل قرية</h3>
                <p class="text-slate-500 font-bold mt-1 text-sm">اختر المركز لعرض عدد البلاغات في كل قرية تابعة له</p>
            </div>
            <form method="GET" action="{{ route('reports.dashboard') }}" class="flex items-center gap-2">
                <input type="hidden" name="from" value="{{ $from }}">
                <input type="hidden" name="to" value="{{ $to }}">
                <input type="hidden" name="status" value="{{ $status }}">
                <select name="village_center" onchange="this.form.submit()"
                    class="w-full md:w-64 rounded-2xl border-slate-200 focus:border-accent focus:ring-accent">
                    <option value="">جميع المراكز (أعلى 15 قرية)</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city->CITY_ID }}" {{ $villageCenter == $city->CITY_ID ? 'selected' : '' }}>{{ $city->CITY_NAME }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-5 py-3 rounded-2xl bg-accent text-white font-bold hover:bg-accent-hover transition-all">
                    <i class="fas fa-filter ml-1"></i>عرض
                </button>
            </form>
        </div>
        <p class="text-slate-400 font-bold mb-3 text-sm"><i class="fas fa-crosshairs ml-1 text-accent"></i>المركز/النطاق المعروض: {{ $chartVillages['center'] }}</p>
        <div class="relative h-80"><canvas id="villagesChart"></canvas></div>
        @if (empty($chartVillages['labels']))
        <p class="text-center text-slate-400 font-bold py-6">لا توجد بلاغات مسجلة على قرى في هذا النطاق</p>
        @endif
    </div>

    {{-- إحصائية حسب جهة البلاغ --}}
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
        <h3 class="text-2xl font-black text-primary mb-1"><i class="fas fa-building-shield ml-2 text-accent"></i>إحصائية حسب جهة البلاغ</h3>
        <p class="text-slate-500 font-bold mb-4 text-sm">أكثر الجهات تسجيلاً للبلاغات وأكثر القرى التي تُبلَّغ عنها (أعلى {{ count($chartAuth['labels']) }} جهات)</p>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="relative h-80"><canvas id="authChart"></canvas></div>
            <div class="overflow-x-auto">
                <table class="w-full text-right">
                    <thead class="bg-slate-50 text-slate-500 text-sm">
                        <tr>
                            <th class="px-4 py-3 font-bold">#</th>
                            <th class="px-4 py-3 font-bold">جهة البلاغ</th>
                            <th class="px-4 py-3 font-bold text-center">عدد البلاغات</th>
                            <th class="px-4 py-3 font-bold text-center">عدد القرى</th>
                            <th class="px-4 py-3 font-bold">أكثر قرية</th>
                            <th class="px-4 py-3 font-bold">أكثر نوع بلاغ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($authorityStats as $item)
                        <tr class="hover:bg-slate-50 transition-colors align-top">
                            <td class="px-4 py-3 font-black text-accent">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 font-bold text-primary whitespace-nowrap">{{ $item['auth'] }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-700">{{ $item['total'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-slate-600">{{ $item['village_count'] }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($item['top_villages'] as $v)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 whitespace-nowrap">{{ $v['village'] }} <span class="text-accent font-black">{{ $v['count'] }}</span></span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($item['top_types'] as $t)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-accent/10 text-accent whitespace-nowrap">{{ $t['type'] }} <span class="font-black">{{ $t['count'] }}</span></span>
                                    @endforeach
                                </div>
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
        </div>
    </div>

    {{-- التوزيع الجغرافي حسب نوع البلاغ: رسم بياني أعمدة مكدسة --}}
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
        <h3 class="text-2xl font-black text-primary mb-1"><i class="fas fa-map-location-dot ml-2 text-accent"></i>التوزيع الجغرافي حسب نوع البلاغ</h3>
        <p class="text-slate-500 font-bold mb-4 text-sm">عدد البلاغات في كل مركز / مدينة مكدسة حسب نوع البلاغ (أعلى {{ count($geoChart['cities']) }} مراكز و {{ count($geoChart['types']) }} أنواع)</p>
        <div class="relative h-96"><canvas id="geoTypesChart"></canvas></div>
    </div>

    {{-- أكثر أنواع البلاغات شيوعاً في كل مركز: رسم بياني نسبي (100%) أفقي --}}
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
        <h3 class="text-2xl font-black text-primary mb-1"><i class="fas fa-chart-bar ml-2 text-accent"></i>أكثر أنواع البلاغات شيوعاً في كل مركز / مدينة</h3>
        <p class="text-slate-500 font-bold mb-4 text-sm">النسبة المئوية لكل نوع بلاغ داخل كل مركز / مدينة (النوع الغالب يظهر كأكبر شريحة ملونة)</p>
        <div class="relative h-96"><canvas id="geoPlacesChart"></canvas></div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined') return;

        Chart.defaults.font.family = "'Cairo', sans-serif";
        Chart.defaults.color = '#64748b';

        // 1) اتجاه يومي
        const dailyCtx = document.getElementById('dailyChart');
        if (dailyCtx && @json($chartDaily['data']).length) {
            new Chart(dailyCtx, {
                type: 'line',
                data: {
                    labels: @json($chartDaily['labels']),
                    datasets: [{
                        label: 'عدد البلاغات',
                        data: @json($chartDaily['data']),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: '#3b82f6',
                        pointRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        }

        // 2) أكثر المراكز
        const citiesCtx = document.getElementById('citiesChart');
        if (citiesCtx && @json($chartCities['data']).length) {
            new Chart(citiesCtx, {
                type: 'bar',
                data: {
                    labels: @json($chartCities['labels']),
                    datasets: [{
                        label: 'عدد البلاغات',
                        data: @json($chartCities['data']),
                        backgroundColor: '#6366f1',
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                        x: { ticks: { maxRotation: 45 } }
                    }
                }
            });
        }

        // 3) أنواع البلاغات
        const typesCtx = document.getElementById('typesChart');
        if (typesCtx && @json($chartTypes['data']).length) {
            const palette = ['#3b82f6', '#22c55e', '#eab308', '#8b5cf6', '#ef4444', '#06b6d4', '#f97316', '#ec4899', '#84cc16', '#64748b'];
            new Chart(typesCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($chartTypes['labels']),
                    datasets: [{
                        data: @json($chartTypes['data']),
                        backgroundColor: @json($chartTypes['labels']).map((_, i) => palette[i % palette.length]),
                        borderWidth: 2,
                        borderColor: '#fff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        // 4) البلاغات في كل قرية
        const villagesCtx = document.getElementById('villagesChart');
        if (villagesCtx && @json($chartVillages['labels']).length) {
            new Chart(villagesCtx, {
                type: 'bar',
                data: {
                    labels: @json($chartVillages['labels']),
                    datasets: [{
                        label: 'عدد البلاغات',
                        data: @json($chartVillages['data']),
                        backgroundColor: '#0ea5e9',
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: {
                        x: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        }

        // 5) إحصائية الأكثر بلاغات حسب جهة البلاغ
        const authCtx = document.getElementById('authChart');
        if (authCtx && @json($chartAuth['data']).length) {
            const authPalette = ['#3b82f6', '#22c55e', '#eab308', '#8b5cf6', '#ef4444', '#06b6d4', '#f97316', '#ec4899', '#84cc16', '#0ea5e9'];
            new Chart(authCtx, {
                type: 'bar',
                data: {
                    labels: @json($chartAuth['labels']),
                    datasets: [{
                        label: 'عدد البلاغات',
                        data: @json($chartAuth['data']),
                        backgroundColor: @json($chartAuth['labels']).map((_, i) => authPalette[i % authPalette.length]),
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: { callbacks: { footer: (items) => 'مجموع: ' + items.reduce((s, it) => s + it.parsed.y, 0) } }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                        x: { ticks: { maxRotation: 45 } }
                    }
                }
            });
        }

        // 6) التوزيع الجغرافي حسب نوع البلاغ (أعمدة مكدسة)
        const geoTypeColors = ['#3b82f6', '#22c55e', '#eab308', '#8b5cf6', '#ef4444', '#06b6d4', '#f97316', '#ec4899'];
        const geoTypesCtx = document.getElementById('geoTypesChart');
        if (geoTypesCtx && @json(count($geoChart['cities'])) > 0) {
            const geoData = @json($geoChart);
            new Chart(geoTypesCtx, {
                type: 'bar',
                data: {
                    labels: geoData.cities,
                    datasets: geoData.types.map((name, i) => ({
                        label: name,
                        data: geoData.counts[i],
                        backgroundColor: geoTypeColors[i % geoTypeColors.length],
                        stack: 'geo',
                        borderRadius: 2,
                    }))
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { font: { family: 'Cairo' } } },
                        tooltip: {
                            callbacks: {
                                footer: (items) => {
                                    const total = items.reduce((s, it) => s + it.parsed.y, 0);
                                    return 'الإجمالي: ' + total;
                                }
                            }
                        }
                    },
                    scales: {
                        x: { stacked: true, ticks: { maxRotation: 45 } },
                        y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        }

        // 7) أكثر أنواع البلاغات شيوعاً في كل مركز (أعمدة مكدسة نسبية % أفقي)
        const geoPlacesCtx = document.getElementById('geoPlacesChart');
        if (geoPlacesCtx && @json(count($geoChart['cities'])) > 0) {
            const geoPlaceData = @json($geoChart);
            new Chart(geoPlacesCtx, {
                type: 'bar',
                data: {
                    labels: geoPlaceData.cities,
                    datasets: geoPlaceData.types.map((name, i) => ({
                        label: name,
                        data: geoPlaceData.percents[i],
                        backgroundColor: geoTypeColors[i % geoTypeColors.length],
                        stack: 'geoPlace',
                        borderRadius: 2,
                    }))
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { font: { family: 'Cairo' } } },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => ctx.dataset.label + ': ' + ctx.parsed.x + '%',
                                footer: (items) => {
                                    const total = items.reduce((s, it) => s + it.parsed.x, 0);
                                    return 'إجمالي النسب: ' + total.toFixed(1) + '%';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            min: 0,
                            max: 100,
                            ticks: { callback: (v) => v + '%' }
                        },
                        y: { stacked: true }
                    }
                }
            });
        }
    });
</script>
@endpush
