@extends('layouts.app')

@section('title', 'تعديل البلاغ ' . $report->REPORT_REGISTER_NUMBER)
@section('page-title', 'تعديل البلاغ')

@section('content')
<div class="max-w-5xl mx-auto w-full">
    <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden form-card">
        <div class="bg-primary p-6 text-white flex items-center justify-between">
            <div>
                <h3 class="text-2xl font-black mb-1 flex items-center gap-3">
                    <i class="fas fa-clipboard-list text-accent"></i>
                    {{ $isLocked ? 'عرض بلاغ رقم' : 'تعديل بلاغ رقم' }} {{ $report->REPORT_REGISTER_NUMBER }}
                </h3>
                <p class="text-slate-300 text-sm">
                    @if ($isLocked)
                    هذا البلاغ تم الانتهاء منه، الشاشة للعرض فقط ولا يمكن التعديل.
                    @else
                    يرجى استيفاء كافة البيانات بدقة لضمان سرعة الاستجابة.
                    @endif
                </p>
            </div>
            <span class="px-4 py-2 bg-white/10 rounded-full font-bold">{{ $report->REQUEST_STATUS }}</span>
        </div>

        @if ($report->REQUEST_STATUS === 'تم الانتهاء')
        <div class="p-4 bg-amber-50 border-b border-amber-200 text-amber-700 text-sm font-bold flex flex-col gap-1">
            <div class="flex items-center gap-2">
                <i class="fas fa-lock"></i>
                تم الانتهاء من هذا البلاغ{{ $isLocked ? '، لا يمكن تعديل بياناته' : '' }}
            </div>
            @if ($report->REPORT_END_DATE)
            <div class="flex items-center gap-2 text-xs font-semibold text-amber-600">
                <i class="fas fa-clock"></i>
                <span>
                    تاريخ القفل: {{ \Illuminate\Support\Carbon::parse($report->REPORT_END_DATE)->format('Y-m-d') }}
                    @if ($report->REPORT_END_TIME)
                    - {{ \Illuminate\Support\Carbon::parse($report->REPORT_END_TIME)->format('H:i') }}
                    @endif
                </span>
                @if ($report->lockedByUser)
                <span>| بواسطة: {{ $report->lockedByUser->name }}</span>
                @endif
            </div>
            @endif
        </div>
        @endif

        <form method="POST" action="{{ route('reports.update', $report) }}" id="report-form" class="p-8 space-y-8">
            @csrf
            @method('PUT')

            @if ($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <fieldset @disabled($isLocked) class="space-y-8">

                {{-- بيانات مقدم البلاغ --}}
                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 space-y-6">
                    <h4 class="font-black text-primary border-r-4 border-accent pr-3">بيانات مقدم البلاغ</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-slate-500 mb-3">مقدم البلاغ <span class="text-red-500">*</span></label>
                            <input type="text" name="REPORTER_NAME" placeholder="الاسم رباعي"
                                value="{{ old('REPORTER_NAME', $report->REPORTER_NAME) }}" required @disabled($isLocked)
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-sm font-bold text-slate-500">رقم الهاتف المحمول <span class="text-red-500">*</span></label>
                                <button type="button" id="phone-toggle"
                                    class="relative inline-flex h-8 w-14 items-center rounded-full bg-slate-300 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-accent/20"
                                    @disabled($isLocked)>
                                    <span id="phone-toggle-dot"
                                        class="inline-block h-6 w-6 transform rounded-full bg-white shadow transition-transform duration-200 translate-x-1"></span>
                                </button>
                            </div>
                            <input type="hidden" name="phone_type" id="phone-type-hidden"
                                value="{{ old('phone_type', substr($report->REPORT_FOLLOWUP_NUMBER, 0, 2) === '01' ? 'mobile' : 'landline') }}">
                            <input type="tel" name="REPORT_FOLLOWUP_NUMBER" id="phone-input"
                                placeholder="01xxxxxxxxx" required @disabled($isLocked)
                                value="{{ old('REPORT_FOLLOWUP_NUMBER', $report->REPORT_FOLLOWUP_NUMBER) }}" maxlength="11"
                                inputmode="numeric"
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-slate-500 mb-3">الرقم القومي <span class="text-red-500">*</span></label>
                            <input type="text" name="REPORTER_SSN" placeholder="أدخل الرقم القومي (14 رقمًا)" required @disabled($isLocked)
                                value="{{ old('REPORTER_SSN', $report->REPORTER_SSN) }}" maxlength="14" pattern="[0-9]{14}" inputmode="numeric"
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                        </div>
                    </div>
                </div>

                {{-- تاريخ ووقت البلاغ - حسب الصلاحيات --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-black text-slate-600">تاريخ البلاغ <span class="text-red-500">*</span></label>
                        <input type="date" name="REPORT_START_DATE" required
                            value="{{ old('REPORT_START_DATE', \Illuminate\Support\Carbon::parse($report->REPORT_START_DATE)->format('Y-m-d')) }}"
                            @unless($canEditDateTime && !$isLocked) readonly @endunless
                            @disabled($isLocked)
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 {{ $canEditDateTime && !$isLocked ? 'bg-slate-50' : 'bg-slate-100 text-slate-400' }} focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-black text-slate-600">وقت البلاغ <span class="text-red-500">*</span></label>
                        <input type="time" name="REPORT_START_TIME" required
                            value="{{ old('REPORT_START_TIME', \Illuminate\Support\Carbon::parse($report->REPORT_START_TIME)->format('H:i')) }}"
                            @unless($canEditDateTime && !$isLocked) readonly @endunless
                            @disabled($isLocked)
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 {{ $canEditDateTime && !$isLocked ? 'bg-slate-50' : 'bg-slate-100 text-slate-400' }} focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                    </div>
                </div>

                {{-- بيانات الحالة والموقع --}}
                <div class="space-y-6">
                    <h4 class="font-black text-primary border-r-4 border-accent pr-3">بيانات الحالة والموقع</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-black text-slate-600">جهة البلاغ <span class="text-red-500">*</span></label>
                            <select name="REPORTING_Auth" id="auth-select" required @disabled($isLocked)
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                                @foreach ($authorities as $source)
                                <option value="{{ $source }}" @selected(old('REPORTING_Auth', $report->REPORTING_Auth) == $source)>{{ $source }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-black text-slate-600">نوع البلاغ <span class="text-red-500">*</span></label>
                            <select name="REPORTING_SORT" id="report-sort-select" required @disabled($isLocked)
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                                @foreach ($reportingTypes as $type)
                                <option value="{{ $type->REPORT_ID }}" @selected(old('REPORTING_SORT', $report->REPORTING_SORT) == $type->REPORT_ID)>{{ $type->REPORT_SORT }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-black text-slate-600">المركز <span class="text-red-500">*</span></label>
                            <select name="CITY" id="city-select" required @disabled($isLocked)
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                                @foreach ($cities as $city)
                                <option value="{{ $city->CITY_ID }}" @selected(old('CITY', $report->CITY) == $city->CITY_ID)>{{ $city->CITY_NAME }}</option>
                                @endforeach
                            </select>
                        </div>

                        @php
                        $currentVillage = $villages->firstWhere('VILLAGE_ID', old('VILLAGE', $report->VILLAGE));
                        $currentLocationType = $currentVillage && str_contains($currentVillage->VILLAGE_SORT, 'مدينة') ? 'مدينة' : 'قرية';
                        @endphp

                        <div class="space-y-2">
                            <label class="block text-sm font-black text-slate-600">نوع الموقع <span class="text-red-500">*</span></label>
                            <div class="flex gap-6 py-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="location_type" value="مدينة" id="rd-madina" class="w-5 h-5 accent-accent"
                                        @checked($currentLocationType==='مدينة' ) @disabled($isLocked)>
                                    <span class="font-bold">مدينة</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="location_type" value="قرية" id="rd-village" class="w-5 h-5 accent-accent"
                                        @checked($currentLocationType==='قرية' ) @disabled($isLocked)>
                                    <span class="font-bold">قرية</span>
                                </label>
                            </div>
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <label class="block text-sm font-black text-slate-600" id="village-label">
                                {{ $currentLocationType === 'مدينة' ? 'المدينة' : 'القرية' }} <span class="text-red-500">*</span>
                            </label>
                            <select name="VILLAGE" id="village-select" required @disabled($isLocked)
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                                @foreach ($villages as $village)
                                <option value="{{ $village->VILLAGE_ID }}"
                                    data-x="{{ $village->X_AXIS }}" data-y="{{ $village->Y_AXIS }}"
                                    @selected(old('VILLAGE', $report->VILLAGE) == $village->VILLAGE_ID)>
                                    {{ $village->VILLAGE_NAME }}
                                </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="X_AXIS" id="x-axis-input" value="{{ old('X_AXIS', $report->X_AXIS) }}">
                            <input type="hidden" name="Y_AXIS" id="y-axis-input" value="{{ old('Y_AXIS', $report->Y_AXIS) }}">
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <label class="block text-sm font-black text-slate-600">تفاصيل الموقع (مكان الحادث بالتفصيل) <span class="text-red-500">*</span></label>
                            <input type="text" name="PLACE_Accident" required @disabled($isLocked)
                                value="{{ old('PLACE_Accident', $report->PLACE_Accident) }}"
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <label class="block text-sm font-black text-slate-600">ملخص البلاغ والوصف الفني <span class="text-red-500">*</span></label>
                            <textarea name="DAMAGE" required rows="4" @disabled($isLocked)
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all disabled:opacity-60 disabled:cursor-not-allowed">{{ old('DAMAGE', $report->DAMAGE) }}</textarea>
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <label class="block text-sm font-black text-slate-600">حالة البلاغ <span class="text-red-500">*</span></label>
                            <select name="REQUEST_STATUS" required @disabled($isLocked)
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                                @foreach ($statuses as $status)
                                {{-- حالة "تم الانتهاء" (قفل البلاغ) تظهر للمشرف العام / الادمن بس --}}
                                @if ($status === 'تم الانتهاء' && !$canLockReport)
                                @continue
                                @endif
                                <option value="{{ $status }}" @selected(old('REQUEST_STATUS', $report->REQUEST_STATUS) == $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                            @if ($canLockReport)
                            <p class="text-xs text-slate-400"> اختيار "تم الانتهاء" يقوم بقفل البلاغ ويسجل تاريخ ووقت القفل والمستخدم الذي قام بالإنهاء.</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- المصابون --}}
                <div class="p-6 bg-blue-50 rounded-2xl border border-blue-100 space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="font-black text-primary border-r-4 border-accent pr-3">بيانات المصابين</h4>
                        @unless ($isLocked)
                        <button type="button" onclick="addInjuredRow()"
                            class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-4 py-2 rounded-lg flex items-center gap-2">
                            <i class="fas fa-plus"></i> إضافة مصاب
                        </button>
                        @endunless
                    </div>
                    <div id="injured-rows" class="space-y-4 max-h-96 overflow-y-auto p-1">
                        @foreach ($report->injuries as $injured)
                        <div class="injured-row grid grid-cols-1 md:grid-cols-4 gap-3 bg-white p-4 rounded-xl border border-blue-100 relative">
                            <input type="text" placeholder="اسم المصاب" value="{{ $injured->INJURED_NAME }}" @disabled($isLocked)
                                name="injured[{{ $loop->index }}][name]" class="px-3 py-2 rounded-lg border border-slate-200 disabled:opacity-60">
                            <input type="number" placeholder="أدخل العمر" value="{{ $injured->INJURED_AGE }}" @disabled($isLocked)
                                name="injured[{{ $loop->index }}][age]" min="1" step="1"
                                onkeydown="return !['e','E','+','-'].includes(event.key)"
                                class="px-3 py-2 rounded-lg border border-slate-200 disabled:opacity-60">
                            <input type="text" placeholder="التشخيص" value="{{ $injured->INJURED_DIAGNOSIS }}" @disabled($isLocked)
                                name="injured[{{ $loop->index }}][diagnosis]" class="px-3 py-2 rounded-lg border border-slate-200 disabled:opacity-60">
                            <div class="flex gap-2">
                                <input type="text" placeholder="المتابعة" value="{{ $injured->INJURED_FOLLOWUP }}" @disabled($isLocked)
                                    name="injured[{{ $loop->index }}][followup]" class="px-3 py-2 rounded-lg border border-slate-200 flex-1 disabled:opacity-60">
                                @unless ($isLocked)
                                <button type="button" class="remove-row bg-red-100 text-red-600 px-3 rounded-lg" onclick="this.closest('.injured-row').remove()">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endunless
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- المتوفون --}}
                <div class="p-6 bg-red-50 rounded-2xl border border-red-100 space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="font-black text-primary border-r-4 border-accent pr-3">بيانات المتوفين</h4>
                        @unless ($isLocked)
                        <button type="button" onclick="addDeceasedRow()"
                            class="bg-red-600 hover:bg-red-700 text-white text-sm font-bold px-4 py-2 rounded-lg flex items-center gap-2">
                            <i class="fas fa-plus"></i> إضافة متوفى
                        </button>
                        @endunless
                    </div>
                    <div id="deceased-rows" class="space-y-4 max-h-96 overflow-y-auto p-1">
                        @foreach ($report->deaths as $deceased)
                        <div class="deceased-row grid grid-cols-1 md:grid-cols-4 gap-3 bg-white p-4 rounded-xl border border-red-100 relative">
                            <input type="text" placeholder="اسم المتوفى" value="{{ $deceased->Deceased_NAME }}" @disabled($isLocked)
                                name="deceased[{{ $loop->index }}][name]" class="px-3 py-2 rounded-lg border border-slate-200 disabled:opacity-60">
                            <input type="number" placeholder="أدخل العمر" value="{{ $deceased->Deceased_AGE }}" @disabled($isLocked)
                                name="deceased[{{ $loop->index }}][age]" min="1" step="1"
                                onkeydown="return !['e','E','+','-'].includes(event.key)"
                                class="px-3 py-2 rounded-lg border border-slate-200 disabled:opacity-60">
                            <input type="text" placeholder="العنوان" value="{{ $deceased->Deceased_ADDRESS }}" @disabled($isLocked)
                                name="deceased[{{ $loop->index }}][address]" class="px-3 py-2 rounded-lg border border-slate-200 disabled:opacity-60">
                            <div class="flex gap-2">
                                <input type="text" placeholder="المتابعة" value="{{ $deceased->Deceased_FOLLOWUP }}" @disabled($isLocked)
                                    name="deceased[{{ $loop->index }}][followup]" class="px-3 py-2 rounded-lg border border-slate-200 flex-1 disabled:opacity-60">
                                @unless ($isLocked)
                                <button type="button" class="remove-row bg-red-100 text-red-600 px-3 rounded-lg" onclick="this.closest('.deceased-row').remove()">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endunless
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- الجهات المخطرة --}}
                <div class="p-6 bg-orange-50 rounded-2xl border border-orange-100 space-y-6">
                    <h4 class="font-black text-primary border-r-4 border-accent pr-3">توجيه الجهات المعنية</h4>
                    @unless ($isLocked)
                    <input type="text" id="authorities-search" placeholder="بحث في الجهات..."
                        class="w-full px-4 py-2 rounded-lg border border-slate-200 bg-white text-sm mb-2">
                    @endunless
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 max-h-52 overflow-y-auto p-1" id="notified-authorities-list">

                        @foreach ($notifiedAuthorities as $authority)
                        <label class="authority-item flex items-center gap-2 cursor-pointer bg-white px-4 py-2 rounded-lg border border-slate-200 hover:border-accent group">
                            <input type="checkbox" name="notified_authorities[]" value="{{ $authority }}" @disabled($isLocked)
                                class="w-5 h-5 accent-accent"
                                @checked(in_array($authority, old('notified_authorities', $selectedAuthorities)))>
                            <span class="font-bold group-hover:text-accent transition-colors authority-name">{{ $authority }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

            </fieldset>

            <div class="pt-6 border-t border-slate-100 flex gap-3">
                @unless ($isLocked)
                <button type="submit"
                    class="flex-1 bg-primary text-white font-black py-4 rounded-2xl border-2 border-transparent hover:border-accent hover:shadow-none shadow-lg text-lg transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-check-double text-accent"></i> حفظ التعديلات
                </button>
                @endunless
                <a href="{{ route('reports.index') }}"
                    class="{{ $isLocked ? 'w-full' : 'flex-1' }} text-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-4 rounded-2xl flex items-center justify-center">
                    {{ $isLocked ? 'رجوع للقائمة' : 'إلغاء' }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

{{-- قوالب الصفوف الجديدة (زي صفحة الإضافة) --}}
<template id="injured-row-template">
    <div class="injured-row grid grid-cols-1 md:grid-cols-4 gap-3 bg-white p-4 rounded-xl border border-blue-100 relative">
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
            <input type="text" placeholder="المتابعة" class="inj-followup px-3 py-2 rounded-lg border border-slate-200 flex-1">
            <button type="button" class="remove-row bg-red-100 text-red-600 px-3 rounded-lg"><i class="fas fa-trash"></i></button>
        </div>
    </div>
</template>

<template id="deceased-row-template">
    <div class="deceased-row grid grid-cols-1 md:grid-cols-4 gap-3 bg-white p-4 rounded-xl border border-red-100 relative">
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
            <input type="text" placeholder="المتابعة" class="dec-followup px-3 py-2 rounded-lg border border-slate-200 flex-1">
            <button type="button" class="remove-row bg-red-100 text-red-600 px-3 rounded-lg"><i class="fas fa-trash"></i></button>
        </div>
    </div>
</template>

@if (!$isLocked)
@push('scripts')
<script>
    // ------------------------------------------------------------------
    // 0. رقم الهاتف: موبايل / أرضي
    // ------------------------------------------------------------------
    const phoneInput = document.getElementById('phone-input');
    const phoneToggle = document.getElementById('phone-toggle');
    const phoneToggleDot = document.getElementById('phone-toggle-dot');
    const phoneTypeHidden = document.getElementById('phone-type-hidden');
    let isMobile = phoneTypeHidden.value === 'mobile';

    function updatePhoneValidation() {
        if (isMobile) {
            phoneInput.maxLength = 11;
            phoneInput.placeholder = '01xxxxxxxxx';
            phoneToggleDot.style.transform = 'translateX(1.5rem)';
            phoneToggle.classList.remove('bg-slate-300');
            phoneToggle.classList.add('bg-accent');
            phoneTypeHidden.value = 'mobile';
        } else {
            phoneInput.maxLength = 10;
            phoneInput.placeholder = '0xxxxxxxxx';
            phoneToggleDot.style.transform = 'translateX(0.125rem)';
            phoneToggle.classList.remove('bg-accent');
            phoneToggle.classList.add('bg-slate-300');
            phoneTypeHidden.value = 'landline';
        }
    }

    phoneToggle.addEventListener('click', function () {
        isMobile = !isMobile;
        updatePhoneValidation();
    });
    updatePhoneValidation();

    // ------------------------------------------------------------------
    // 1. جهة البلاغ -> تحميل أنواع البلاغات المرتبطة بيها
    // ------------------------------------------------------------------
    const authSelect = document.getElementById('auth-select');
    const reportSortSelect = document.getElementById('report-sort-select');
    const currentReportSort = @json(old('REPORTING_SORT', $report->REPORTING_SORT));

    function loadReportTypes(authValue, preselect = null) {
        reportSortSelect.innerHTML = '<option value="">جاري التحميل...</option>';
        fetch(`{{ route('reports.report-types-by-auth') }}?auth=${encodeURIComponent(authValue)}`)
            .then(res => res.json())
            .then(types => {
                reportSortSelect.innerHTML = '<option value="" disabled>اختر نوع البلاغ</option>';
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
    const currentVillage = @json(old('VILLAGE', $report->VILLAGE));
    let firstLoad = true;

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
                villageSelect.innerHTML = '<option value="" disabled>' +
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

    // أول تحميل: منسيبش الـ village اللي جاي من السيرفر لحد ما المستخدم يغيّر المركز أو نوع الموقع
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
    // 4. صفوف المصابين/المتوفين الجديدة (اللي موجودة أصلاً بتترسم من Blade فوق)
    // ------------------------------------------------------------------
    let injuredCount = {{ $report->injuries->count() }};
    let deceasedCount = {{ $report->deaths->count() }};

    function addInjuredRow() {
        const template = document.getElementById('injured-row-template');
        const clone = template.content.cloneNode(true);
        const index = injuredCount++;
        const wrapper = clone.querySelector('.injured-row');

        wrapper.querySelector('.inj-name').name = `injured[${index}][name]`;
        wrapper.querySelector('.inj-birth').name = `injured[${index}][age]`;
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
        wrapper.querySelector('.dec-birth').name = `deceased[${index}][age]`;
        wrapper.querySelector('.dec-address').name = `deceased[${index}][address]`;
        wrapper.querySelector('.dec-followup').name = `deceased[${index}][followup]`;
        wrapper.querySelector('.remove-row').addEventListener('click', () => wrapper.remove());

        document.getElementById('deceased-rows').appendChild(clone);
    }

    // إعادة تحميل الاختيارات بعد خطأ Validation (لو رجع لنفس الصفحة)
    @if($errors->any())
    loadReportTypes(authSelect.value, currentReportSort);
    @endif
</script>
@endpush
@endif