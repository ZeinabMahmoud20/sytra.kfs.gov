@extends('layouts.app')

@section('title', 'قائمة البلاغات - الشبكة الوطنية للطوارئ')
@section('page-title', 'قائمة البلاغات')

@section('content')
<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <h1 class="text-2xl font-black text-primary">كل البلاغات</h1>
    <div class="flex items-center gap-3 flex-wrap">
        <a href="{{ route('reports.export.excel', request()->query()) }}"
            class="bg-green-600 hover:bg-green-700 text-white font-bold px-4 py-3 rounded-xl transition-all flex items-center gap-2 text-sm">
            <i class="fas fa-file-excel"></i> تصدير Excel
        </a>
        <a href="{{ route('reports.export.pdf', request()->query()) }}"
            class="bg-red-600 hover:bg-red-700 text-white font-bold px-4 py-3 rounded-xl transition-all flex items-center gap-2 text-sm">
            <i class="fas fa-file-pdf"></i> تصدير PDF
        </a>
        <a href="{{ route('reports.create') }}"
            class="bg-accent hover:bg-accent-hover text-white font-bold px-5 py-3 rounded-xl transition-all flex items-center gap-2">
            <i class="fas fa-plus"></i> إضافة بلاغ جديد
        </a>
    </div>
</div>

{{-- زرار طي/فتح الفلاتر --}}
<button type="button" id="toggle-filters-btn"
    class="mb-3 flex items-center gap-2 text-primary font-bold bg-white border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">
    <i class="fas fa-filter"></i>
    <span>الفلاتر</span>
    <i id="toggle-filters-icon" class="fas fa-chevron-down text-xs transition-transform"></i>
</button>

{{-- فورم الفلاتر - مقفول افتراضياً --}}
<form method="GET" action="{{ route('reports.index') }}" id="filters-form" id="global-search-form"
    class="hidden bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mb-6 space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div class="space-y-1">
            <label class="text-xs font-bold text-slate-500">اسم صاحب البلاغ</label>
            <input type="text" name="reporter_name" value="{{ request('reporter_name') }}"
                class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
        </div>
        <div class="space-y-1">
            <label class="text-xs font-bold text-slate-500">الرقم القومي</label>
            <input type="text" name="national_id" value="{{ request('national_id') }}"
                class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
        </div>
        <div class="space-y-1">
            <label class="text-xs font-bold text-slate-500">رقم الهاتف</label>
            <input type="text" name="phone" value="{{ request('phone') }}"
                class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
        </div>
        <div class="space-y-1">
            <label class="text-xs font-bold text-slate-500">المركز</label>
            <select name="city" id="filter-city"
                class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                <option value="">اختر المركز</option>
                @foreach ($cities as $city)
                <option value="{{ $city->CITY_ID }}" @selected(request('city')==$city->CITY_ID)>{{ $city->CITY_NAME }}</option>
                @endforeach
            </select>
        </div>
        <div class="space-y-1">
            <label class="text-xs font-bold text-slate-500">المدينة</label>
            <select name="madina" id="filter-madina"
                class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                <option value="">اختر المدينة</option>
            </select>
        </div>
        <div class="space-y-1">
            <label class="text-xs font-bold text-slate-500">القرية</label>
            <select name="village" id="filter-village"
                class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                <option value="">اختر القرية</option>
            </select>
        </div>
        <div class="space-y-1">
            <label class="text-xs font-bold text-slate-500">جهة البلاغ</label>
            <select name="reporting_auth"
                class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                <option value="">اختر جهة البلاغ</option>
                @foreach ($authorities as $auth)
                <option value="{{ $auth }}" @selected(request('reporting_auth')==$auth)>{{ $auth }}</option>
                @endforeach
            </select>
        </div>
        <div class="space-y-1">
            <label class="text-xs font-bold text-slate-500">نوع البلاغ</label>
            <select name="reporting_sort"
                class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                <option value="">اختر نوع البلاغ</option>
                @foreach ($reportingTypes as $type)
                <option value="{{ $type->REPORT_ID }}" @selected(request('reporting_sort')==$type->REPORT_ID)>{{ $type->REPORT_SORT }}</option>
                @endforeach
            </select>
        </div>
        <div class="space-y-1">
            <label class="text-xs font-bold text-slate-500">حالة البلاغ</label>
            <select name="status"
                class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                <option value="">---الكل---</option>
                @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(request('status')==$status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="space-y-1">
            <label class="text-xs font-bold text-slate-500">من تاريخ</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
        </div>
        <div class="space-y-1">
            <label class="text-xs font-bold text-slate-500">إلى تاريخ</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
        </div>
    </div>

    <div class="flex items-center gap-4 pt-2 border-t border-slate-100 flex-wrap">
        <label class="flex items-center gap-2 text-sm font-bold text-slate-600 cursor-pointer">
            <input type="checkbox" name="filter_by_time" value="1" @checked(request()->boolean('filter_by_time')) class="w-4 h-4 accent-accent">
            فلترة بالوقت
        </label>
        <input type="time" name="time_from" value="{{ request('time_from') }}"
            class="px-3 py-2 rounded-lg border border-slate-200 text-sm">
        <span class="text-slate-400 text-sm">إلى</span>
        <input type="time" name="time_to" value="{{ request('time_to') }}"
            class="px-3 py-2 rounded-lg border border-slate-200 text-sm">

        <div class="mr-auto flex gap-2">
            <a href="{{ route('reports.index') }}"
                class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold px-4 py-2 rounded-lg text-sm">إلغاء الفلاتر</a>
            <button type="submit"
                class="bg-primary hover:bg-primary/90 text-white font-bold px-6 py-2 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-filter"></i> بحث
            </button>
        </div>
    </div>
</form>
<div class="mb-4">
    <form method="GET" action="{{ route('reports.index') }}">
        {{-- الاحتفاظ بجميع الفلاتر الحالية --}}
        @foreach(request()->except('search', 'page') as $key => $value)
        @if(is_array($value))
        @foreach($value as $v)
        <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
        @endforeach
        @else
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
        @endforeach

        <div class="relative">
            <input
                id="global-search"
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="بحث في جميع البلاغات..."
                class="w-full rounded-xl border border-slate-200 py-3 pr-12 pl-28 focus:ring-2 focus:ring-accent focus:border-accent outline-none">
            <i class="fas fa-search absolute right-4 top-1/2 -translate-y-1/2 text-slate-400"></i>

            <button
                class="hidden">
                بحث
            </button>
        </div>
    </form>
</div>
<div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-right whitespace-nowrap">
            <thead class="bg-slate-50 text-slate-500 text-sm">
                <tr>
                    <th class="px-4 py-4 font-bold">الرقم القومي</th>
                    <th class="px-4 py-4 font-bold">رقم قيد البلاغ</th>
                    <th class="px-4 py-4 font-bold">متلقي البلاغ</th>
                    <th class="px-4 py-4 font-bold">اسم المبلغ</th>
                    <th class="px-4 py-4 font-bold">جهة البلاغ</th>
                    <th class="px-4 py-4 font-bold">نوع البلاغ</th>
                    <th class="px-4 py-4 font-bold">المركز</th>
                    <th class="px-4 py-4 font-bold">مكان البلاغ</th>
                    <th class="px-4 py-4 font-bold">تاريخ تقديم البلاغ</th>
                    <th class="px-4 py-4 font-bold">وقت تقديم البلاغ</th>
                    <th class="px-4 py-4 font-bold text-center">عدد المصابين</th>
                    <th class="px-4 py-4 font-bold text-center">عدد الوفيات</th>
                    <th class="px-4 py-4 font-bold text-center">حالة البلاغ</th>
                    <th class="px-4 py-4 font-bold">تاريخ انتهاء البلاغ</th>
                    <th class="px-4 py-4 font-bold">وقت انتهاء البلاغ</th>
                    <th class="px-4 py-4 font-bold">رقم تليفون</th>
                    <th class="px-4 py-4 font-bold text-center">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($reports as $report)
                @php
                $statusClasses = match ($report->REQUEST_STATUS) {
                'تم استلام البلاغ' => 'bg-red-100 text-red-600',
                'قيد المعالجة' => 'bg-yellow-100 text-yellow-700',
                'تم التنفيذ', 'تم الانتهاء' => 'bg-green-100 text-green-700',
                default => 'bg-slate-100 text-slate-600',
                };
                @endphp
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-4 text-slate-500">{{ $report->REPORTER_SSN }}</td>
                    <td class="px-4 py-4 font-bold text-primary">{{ $report->REPORT_REGISTER_NUMBER }}</td>
                    <td class="px-4 py-4 text-slate-500">{{ $report->user->name ?? 'online' }}</td>
                    <td class="px-4 py-4">{{ $report->REPORTER_NAME }}</td>
                    <td class="px-4 py-4 text-slate-500">{{ $report->REPORTING_Auth ?? optional($report->reportingType)->AUTHORITY }}</td>
                    <td class="px-4 py-4 font-semibold">{{ $report->reportingType->REPORT_SORT ?? '-' }}</td>
                    <td class="px-4 py-4 text-slate-500">{{ $report->city->CITY_NAME ?? '-' }}</td>
                    <td class="px-4 py-4 text-slate-500">{{ $report->village->VILLAGE_NAME ?? '-' }}</td>
                    <td class="px-4 py-4 text-slate-500">{{ $report->REPORT_START_DATE }}</td>
                    <td class="px-4 py-4 text-slate-500">{{ $report->REPORT_START_TIME }}</td>
                    <td class="px-4 py-4 text-center">{{ $report->INFECTED_NUM ?? 0 }}</td>
                    <td class="px-4 py-4 text-center">{{ $report->Deceased_Num ?? 0 }}</td>
                    <td class="px-4 py-4 text-center">
                        <span class="px-3 py-1 rounded-full text-sm font-black {{ $statusClasses }}">
                            {{ $report->REQUEST_STATUS }}
                        </span>
                    </td>
                    <td class="px-4 py-4 text-slate-500">{{ $report->REPORT_END_DATE }}</td>
                    <td class="px-4 py-4 text-slate-500">{{ $report->REPORT_END_TIME }}</td>
                    <td class="px-4 py-4 text-slate-500">{{ $report->REPORT_FOLLOWUP_NUMBER }}</td>
                    <td class="px-4 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('reports.show', $report) }}" title="عرض"
                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100">
                                <i class="fas fa-eye text-sm"></i>
                            </a>
                            <a href="{{ route('reports.edit', $report) }}" title="تعديل"
                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100">
                                <i class="fas fa-edit text-sm"></i>
                            </a>
                            <a href="{{ route('reports.attachments.create', $report) }}" title="إضافة مرفق"
                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-purple-50 text-purple-600 hover:bg-purple-100">
                                <i class="fas fa-paperclip text-sm"></i>
                            </a>
                            <a href="{{ route('reports.show', $report) }}#attachments" title="عرض المرفقات"
                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200">
                                <i class="fas fa-folder-open text-sm"></i>
                            </a>
                            <form method="POST" action="{{ route('reports.destroy', $report) }}"
                                onsubmit="return confirm('متأكد إنك عايز تحذف البلاغ ده؟ الإجراء ده مش قابل للتراجع.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="حذف"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="17" class="px-6 py-8 text-center text-slate-400">لا توجد بلاغات مطابقة</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    {{ $reports->links() }}
</div>
@endsection

@push('scripts')
<script>
    // ------------------------------------------------------------------
    // طي/فتح قسم الفلاتر - مقفول افتراضياً
    // ------------------------------------------------------------------
    const filtersForm = document.getElementById('filters-form');
    const toggleBtn = document.getElementById('toggle-filters-btn');
    const toggleIcon = document.getElementById('toggle-filters-icon');

    // لو فيه فلاتر متطبقة فعلاً (جاي من رابط فيه query string)، افتح القسم تلقائي
    const hasActiveFilters = window.location.search.length > 1;
    if (hasActiveFilters) {
        filtersForm.classList.remove('hidden');
        toggleIcon.classList.add('rotate-180');
    }

    toggleBtn.addEventListener('click', function() {
        filtersForm.classList.toggle('hidden');
        toggleIcon.classList.toggle('rotate-180');
    });

    // ------------------------------------------------------------------
    // المدينة والقرية متبادلين مش مع بعض (زي الديسكتوب بالظبط)
    // ------------------------------------------------------------------
    const filterCity = document.getElementById('filter-city');
    const filterMadina = document.getElementById('filter-madina');
    const filterVillage = document.getElementById('filter-village');

    filterMadina.addEventListener('change', function() {
        if (this.value) filterVillage.value = '';
    });
    filterVillage.addEventListener('change', function() {
        if (this.value) filterMadina.value = '';
    });

    function loadFilterLocations(preselectMadina = null, preselectVillage = null) {
        if (!filterCity.value) return;

        fetch(`/reports/villages-by-city/${filterCity.value}?type=مدينة`)
            .then(res => res.json())
            .then(list => {
                filterMadina.innerHTML = '<option value="">اختر المدينة</option>';
                list.forEach(v => {
                    const opt = document.createElement('option');
                    opt.value = v.VILLAGE_ID;
                    opt.textContent = v.VILLAGE_NAME;
                    if (preselectMadina && String(preselectMadina) === String(v.VILLAGE_ID)) opt.selected = true;
                    filterMadina.appendChild(opt);
                });
            });

        fetch(`/reports/villages-by-city/${filterCity.value}?type=قرية`)
            .then(res => res.json())
            .then(list => {
                filterVillage.innerHTML = '<option value="">اختر القرية</option>';
                list.forEach(v => {
                    const opt = document.createElement('option');
                    opt.value = v.VILLAGE_ID;
                    opt.textContent = v.VILLAGE_NAME;
                    if (preselectVillage && String(preselectVillage) === String(v.VILLAGE_ID)) opt.selected = true;
                    filterVillage.appendChild(opt);
                });
            });
    }

    filterCity.addEventListener('change', () => loadFilterLocations());

    @if(request('city'))
    loadFilterLocations(@json(request('madina')), @json(request('village')));
    @endif

    // ==========================================
// البحث التلقائي (يشبه Excel)
// ==========================================

const globalSearch = document.getElementById('global-search');
const globalSearchForm = document.getElementById('global-search-form');

if (globalSearch) {

    let typingTimer;

    globalSearch.addEventListener('input', function () {

        clearTimeout(typingTimer);

        typingTimer = setTimeout(function () {

            globalSearchForm.submit();

        }, 500); // نصف ثانية بعد التوقف عن الكتابة

    });

}
</script>
@endpush