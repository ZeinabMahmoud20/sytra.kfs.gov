@extends('layouts.app')

@section('title', 'إضافة بلاغ جديد - الشبكة الوطنية للطوارئ')
@section('page-title', 'إضافة بلاغ جديد')

@section('content')
<div class="max-w-5xl mx-auto w-full">
    <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden form-card">
        <div class="bg-primary p-6 text-white flex items-center justify-between">
            <div>
                <h3 class="text-2xl font-black mb-1 flex items-center gap-3">
                    <i class="fas fa-clipboard-list text-accent"></i> استمارة تسجيل بلاغ
                </h3>
                <p class="text-slate-300 text-sm">يرجى استيفاء كافة البيانات بدقة لضمان سرعة الاستجابة.</p>
            </div>
            <div class="text-left">
                <span class="text-xs opacity-60 uppercase block">رقم البلاغ التلقائي</span>
                <span class="text-xl font-mono font-bold">{{ $nextRegisterNumber ?? '---' }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('reports.store') }}" id="report-form" class="p-8 space-y-8">
            @csrf

            @if ($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            {{-- بيانات مقدم البلاغ --}}
            <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 space-y-6">
                <h4 class="font-black text-primary border-r-4 border-accent pr-3">بيانات مقدم البلاغ</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-500">مقدم البلاغ <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="REPORTER_NAME" placeholder="الاسم رباعي"
                            value="{{ old('REPORTER_NAME') }}" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-500">رقم الهاتف <span
                                class="text-red-500">*</span></label>
                        <input type="tel" name="REPORT_FOLLOWUP_NUMBER" placeholder="01xxxxxxxxx" required
                            value="{{ old('REPORT_FOLLOWUP_NUMBER') }}" pattern="^01[0125][0-9]{8}$" maxlength="11"
                            inputmode="numeric"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-500">الرقم القومي <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="REPORTER_SSN" placeholder="أدخل الرقم القومي (14 رقمًا)" required
                            value="{{ old('REPORTER_SSN') }}" maxlength="14" pattern="[0-9]{14}" inputmode="numeric"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                    </div>
                </div>
            </div>

            {{-- تاريخ ووقت البلاغ - قابل للتعديل للمشرف بس --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-sm font-black text-slate-600">تاريخ البلاغ <span
                            class="text-red-500">*</span></label>
                    <input type="date" name="REPORT_START_DATE" required
                        value="{{ old('REPORT_START_DATE', now()->format('Y-m-d')) }}" @unless($canEditDateTime)
                        readonly @endunless
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 {{ $canEditDateTime ? 'bg-slate-50' : 'bg-slate-100 text-slate-400' }} focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-black text-slate-600">وقت البلاغ <span
                            class="text-red-500">*</span></label>
                    <input type="time" name="REPORT_START_TIME" required
                        value="{{ old('REPORT_START_TIME', now()->format('H:i')) }}" @unless($canEditDateTime) readonly
                        @endunless
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 {{ $canEditDateTime ? 'bg-slate-50' : 'bg-slate-100 text-slate-400' }} focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                </div>
            </div>

            {{-- بيانات الحالة والموقع --}}
            <div class="space-y-6">
                <h4 class="font-black text-primary border-r-4 border-accent pr-3">بيانات الحالة والموقع</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- جهة البلاغ - ديناميكي من REPORTING_TYPES --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-black text-slate-600">جهة البلاغ <span
                                class="text-red-500">*</span></label>
                        <select name="REPORTING_Auth" id="auth-select" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                            <option value="" disabled {{ old('REPORTING_Auth') ? '' : 'selected' }}>اختر الجهة</option>
                            @foreach ($authorities as $source)
                            <option value="{{ $source }}" @selected(old('REPORTING_Auth')==$source)>{{ $source }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- نوع البلاغ - بيتحمل حسب جهة البلاغ المختارة --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-black text-slate-600">نوع البلاغ <span
                                class="text-red-500">*</span></label>
                        <select name="REPORTING_SORT" id="report-sort-select" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                            <option value="" selected>اختر جهة البلاغ أولاً</option>
                        </select>
                    </div>

                    {{-- المركز --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-black text-slate-600">المركز <span
                                class="text-red-500">*</span></label>
                        <select name="CITY" id="city-select" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                            <option value="" disabled {{ old('CITY') ? '' : 'selected' }}>اختر المركز</option>
                            @foreach ($cities as $city)
                            <option value="{{ $city->CITY_ID }}" @selected(old('CITY')==$city->
                                CITY_ID)>{{ $city->CITY_NAME }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- مدينة/قرية + القائمة المرتبطة --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-black text-slate-600">نوع الموقع <span
                                class="text-red-500">*</span></label>
                        <div class="flex gap-6 py-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="location_type" value="مدينة" id="rd-madina"
                                    class="w-5 h-5 accent-accent" checked>
                                <span class="font-bold">مدينة</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="location_type" value="قرية" id="rd-village"
                                    class="w-5 h-5 accent-accent">
                                <span class="font-bold">قرية</span>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="block text-sm font-black text-slate-600" id="village-label">المدينة <span
                                class="text-red-500">*</span></label>
                        <select name="VILLAGE" id="village-select" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                            <option value="" selected>اختر المركز أولاً</option>
                        </select>
                        {{-- إحداثيات الموقع بتتحط تلقائي من اختيار المدينة/القرية --}}
                        <input type="hidden" name="X_AXIS" id="x-axis-input" value="{{ old('X_AXIS', 0) }}">
                        <input type="hidden" name="Y_AXIS" id="y-axis-input" value="{{ old('Y_AXIS', 0) }}">
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="block text-sm font-black text-slate-600">تفاصيل الموقع (مكان الحادث بالتفصيل)
                            <span class="text-red-500">*</span></label>
                        <input type="text" name="PLACE_Accident" required value="{{ old('PLACE_Accident') }}"
                            placeholder="مثال: بجوار مدرسة التجارة، برج النيل، شقة رقم..."
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="block text-sm font-black text-slate-600">ملخص البلاغ والوصف الفني <span
                                class="text-red-500">*</span></label>
                        <textarea name="DAMAGE" required rows="4" placeholder="اكتب في نقاط سريعة ماذا حدث بالضبط..."
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">{{ old('DAMAGE') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- المصابون --}}
            <div class="p-6 bg-blue-50 rounded-2xl border border-blue-100 space-y-4" x-data="{ rows: [] }">
                <div class="flex items-center justify-between">
                    <h4 class="font-black text-primary border-r-4 border-accent pr-3">بيانات المصابين (اختياري)</h4>
                    <button type="button" onclick="addInjuredRow()"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fas fa-plus"></i> إضافة مصاب
                    </button>
                </div>
                <div id="injured-rows" class="space-y-4"></div>
            </div>

            {{-- المتوفون --}}
            <div class="p-6 bg-red-50 rounded-2xl border border-red-100 space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="font-black text-primary border-r-4 border-accent pr-3">بيانات المتوفين (اختياري)</h4>
                    <button type="button" onclick="addDeceasedRow()"
                        class="bg-red-600 hover:bg-red-700 text-white text-sm font-bold px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fas fa-plus"></i> إضافة متوفى
                    </button>
                </div>
                <div id="deceased-rows" class="space-y-4"></div>
            </div>

            {{-- الجهات المخطرة - ديناميكي من NotifiedAuthTBL --}}
            <div class="p-6 bg-orange-50 rounded-2xl border border-orange-100 space-y-6">
                <h4 class="font-black text-primary border-r-4 border-accent pr-3">توجيه الجهات المعنية</h4>
                <input type="text" id="authorities-search" placeholder="بحث في الجهات..."
                    class="w-full px-4 py-2 rounded-lg border border-slate-200 bg-white text-sm mb-2">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 max-h-52 overflow-y-auto p-1" id="notified-authorities-list">
                    @foreach ($notifiedAuthorities as $authority)
                    <label
                        class="authority-item flex items-center gap-2 cursor-pointer bg-white px-4 py-2 rounded-lg border border-slate-200 hover:border-accent group">
                        <input type="checkbox" name="notified_authorities[]" value="{{ $authority }}"
                            class="w-5 h-5 accent-accent" @checked(in_array($authority, old('notified_authorities',
                            [])))>
                        <span
                            class="font-bold group-hover:text-accent transition-colors authority-name">{{ $authority }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100">
                <button type="submit"
                    class="w-full bg-primary text-white font-black py-4 rounded-2xl border-2 border-transparent hover:border-accent hover:shadow-none shadow-lg text-lg transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-check-double text-accent"></i> حفظ وإرسال البلاغ
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

{{-- قالب صف مصاب/متوفى مخفي، بنستخدمه كـ Template لأي صف جديد --}}
<template id="injured-row-template">
    <div
        class="injured-row grid grid-cols-1 md:grid-cols-4 gap-3 bg-white p-4 rounded-xl border border-blue-100 relative">
        <input type="text" placeholder="اسم المصاب" class="inj-name px-3 py-2 rounded-lg border border-slate-200">
        <input
            type="number"
            placeholder="أدخل العمر"
            class="inj-birth px-3 py-2 rounded-lg border border-slate-200"
            min="1"
            step="1"
            required
            onkeydown="return !['e','E','+','-'].includes(event.key)">
        <input type="text" placeholder="التشخيص" class="inj-diagnosis px-3 py-2 rounded-lg border border-slate-200">
        <div class="flex gap-2">
            <input type="text" placeholder="المتابعة"
                class="inj-followup px-3 py-2 rounded-lg border border-slate-200 flex-1">
            <button type="button" class="remove-row bg-red-100 text-red-600 px-3 rounded-lg"><i
                    class="fas fa-trash"></i></button>
        </div>
    </div>
</template>

<template id="deceased-row-template">
    <div
        class="deceased-row grid grid-cols-1 md:grid-cols-4 gap-3 bg-white p-4 rounded-xl border border-red-100 relative">
        <input type="text" placeholder="اسم المتوفى" class="dec-name px-3 py-2 rounded-lg border border-slate-200">
        <input
            type="number"
            placeholder="أدخل العمر"
            class="dec-birth px-3 py-2 rounded-lg border border-slate-200"
            min="1"
            step="1"
            required
            onkeydown="return !['e','E','+','-'].includes(event.key)">
        <input type="text" placeholder="العنوان" class="dec-address px-3 py-2 rounded-lg border border-slate-200">
        <div class="flex gap-2">
            <input type="text" placeholder="المتابعة"
                class="dec-followup px-3 py-2 rounded-lg border border-slate-200 flex-1">
            <button type="button" class="remove-row bg-red-100 text-red-600 px-3 rounded-lg"><i
                    class="fas fa-trash"></i></button>
        </div>
    </div>
</template>

@push('scripts')
<script>
    // ------------------------------------------------------------------
    // 1. جهة البلاغ -> تحميل أنواع البلاغات المرتبطة بيها
    // ------------------------------------------------------------------
    const authSelect = document.getElementById('auth-select');
    const reportSortSelect = document.getElementById('report-sort-select');

    function loadReportTypes(authValue, preselect = null) {
        reportSortSelect.innerHTML = '<option value="">جاري التحميل...</option>';
        fetch(`{{ route('reports.report-types-by-auth') }}?auth=${encodeURIComponent(authValue)}`)
            .then(res => res.json())
            .then(types => {
                reportSortSelect.innerHTML = '<option value="" disabled selected>اختر نوع البلاغ</option>';
                types.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t.REPORT_ID;
                    opt.textContent = t.REPORT_SORT;
                    if (preselect && String(preselect) === String(t.REPORT_ID)) opt.selected = true;
                    reportSortSelect.appendChild(opt);
                });
            });
    }

    authSelect.addEventListener('change', function() {
        if (this.value) loadReportTypes(this.value);
    });

    // ------------------------------------------------------------------
    // 2. المركز + نوع الموقع (مدينة/قرية) -> تحميل القرى/المدن المرتبطة
    // ------------------------------------------------------------------
    const citySelect = document.getElementById('city-select');
    const rdMadina = document.getElementById('rd-madina');
    const rdVillage = document.getElementById('rd-village');
    const villageSelect = document.getElementById('village-select');
    const villageLabel = document.getElementById('village-label');
    const xAxisInput = document.getElementById('x-axis-input');
    const yAxisInput = document.getElementById('y-axis-input');

    function currentLocationType() {
        return rdVillage.checked ? 'قرية' : 'مدينة';
    }

    function loadVillages(preselect = null) {
        if (!citySelect.value) return;
        villageLabel.textContent = (currentLocationType() === 'مدينة' ? 'المدينة' : 'القرية') + ' *';
        villageSelect.innerHTML = '<option value="">جاري التحميل...</option>';

        fetch(`/reports/villages-by-city/${citySelect.value}?type=${encodeURIComponent(currentLocationType())}`)
            .then(res => res.json())
            .then(villages => {
                villageSelect.innerHTML = '<option value="" disabled selected>' +
                    (currentLocationType() === 'مدينة' ? 'اختر المدينة' : 'اختر القرية') + '</option>';
                villages.forEach(v => {
                    const opt = document.createElement('option');
                    opt.value = v.VILLAGE_ID;
                    opt.textContent = v.VILLAGE_NAME;
                    opt.dataset.x = v.X_AXIS ?? 0;
                    opt.dataset.y = v.Y_AXIS ?? 0;
                    if (preselect && String(preselect) === String(v.VILLAGE_ID)) opt.selected = true;
                    villageSelect.appendChild(opt);
                });
            });
    }

    citySelect.addEventListener('change', () => loadVillages());
    rdMadina.addEventListener('change', () => loadVillages());
    rdVillage.addEventListener('change', () => loadVillages());

    villageSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        xAxisInput.value = selected?.dataset?.x ?? 0;
        yAxisInput.value = selected?.dataset?.y ?? 0;
    });

    // ------------------------------------------------------------------
    // 3. بحث في الجهات المخطرة
    // ------------------------------------------------------------------
    document.getElementById('authorities-search')?.addEventListener('input', function() {
        const term = this.value.trim().toLowerCase();
        document.querySelectorAll('.authority-item').forEach(item => {
            const name = item.querySelector('.authority-name').textContent.toLowerCase();
            item.style.display = name.includes(term) ? '' : 'none';
        });
    });

    // ------------------------------------------------------------------
    // 4. صفوف المصابين/المتوفين الديناميكية
    // ------------------------------------------------------------------
    let injuredCount = 0;
    let deceasedCount = 0;

    function addInjuredRow() {
        const template = document.getElementById('injured-row-template');
        const clone = template.content.cloneNode(true);
        const index = injuredCount++;
        const wrapper = clone.querySelector('.injured-row');

        wrapper.querySelector('.inj-name').name = `injured[${index}][name]`;
        wrapper.querySelector('.inj-birth').name = `injured[${index}][birth_date]`;
        wrapper.querySelector('.inj-diagnosis').name = `injured[${index}][diagnosis]`;
        wrapper.querySelector('.inj-followup').name = `injured[${index}][followup]`;
        wrapper.querySelector('.remove-row').addEventListener('click', () => wrapper.remove());

        document.getElementById('injured-rows').appendChild(clone);
    }

    function addDeceasedRow() {
        const template = document.getElementById('deceased-row-template');
        const clone = template.content.cloneNode(true);
        const index = deceasedCount++;
        const wrapper = clone.querySelector('.deceased-row');

        wrapper.querySelector('.dec-name').name = `deceased[${index}][name]`;
        wrapper.querySelector('.dec-birth').name = `deceased[${index}][birth_date]`;
        wrapper.querySelector('.dec-address').name = `deceased[${index}][address]`;
        wrapper.querySelector('.dec-followup').name = `deceased[${index}][followup]`;
        wrapper.querySelector('.remove-row').addEventListener('click', () => wrapper.remove());

        document.getElementById('deceased-rows').appendChild(clone);
    }

    // إعادة تحميل الاختيارات القديمة بعد خطأ Validation (لو المستخدم رجع لنفس الصفحة)
    @if(old('REPORTING_Auth'))
    loadReportTypes(@json(old('REPORTING_Auth')), @json(old('REPORTING_SORT')));
    @endif
    @if(old('CITY'))
    citySelect.value = @json(old('CITY'));
    @if(old('location_type') === 'قرية')
    rdVillage.checked = true;
    @endif
    loadVillages(@json(old('VILLAGE')));
    @endif
</script>
@endpush