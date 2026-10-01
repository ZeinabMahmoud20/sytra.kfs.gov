@extends('layouts.app')

@section('title', 'إضافة إشارة جديدة - الشبكة الوطنية للطوارئ')
@section('page-title', 'تسجيل إشارة جديدة')

@section('content')
    <div class="max-w-5xl mx-auto w-full">
        <a href="{{ route('signals.index') }}"
            class="inline-flex items-center gap-2 text-primary font-bold mb-4 hover:text-accent transition-colors">
            <i class="fas fa-arrow-right"></i> رجوع لقائمة الإشارات
        </a>

        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="bg-primary p-6 text-white flex items-center justify-between">
                <h3 class="text-2xl font-black flex items-center gap-3">
                    <i class="fas fa-broadcast-tower text-accent"></i> تسجيل إشارة جديدة
                </h3>
                <div class="text-left">
                    <span class="text-xs opacity-60 uppercase block">كود الإشارة التلقائي</span>
                    <span class="text-xl font-mono font-bold">{{ $nextSignalCode }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('signals.store') }}" id="signal-form" class="p-8 space-y-6">
                @csrf

                @if ($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div id="signal-cards" class="space-y-6"></div>

                <button type="button" onclick="addSignalCard()"
                    class="w-full border-2 border-dashed border-accent/40 hover:border-accent text-accent font-bold py-4 rounded-2xl flex items-center justify-center gap-2 transition-all">
                    <i class="fas fa-plus"></i> إضافة كارت إشارة
                </button>

                <div class="pt-6 border-t border-slate-100">
                    <button type="submit"
                        class="w-full bg-primary text-white font-black py-4 rounded-2xl border-2 border-transparent hover:border-accent shadow-lg text-lg transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-save text-accent"></i> حفظ بيانات الإشارة
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

{{-- قالب كارت الإشارة - بيتكرر لكل كارت جديد --}}
<template id="signal-card-template">
    <div class="signal-card bg-slate-50 rounded-2xl border border-slate-200 p-6 space-y-6 relative">
        <button type="button" class="remove-card absolute left-4 top-4 text-red-400 hover:text-red-600">
            <i class="fas fa-times-circle text-xl"></i>
        </button>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="space-y-1">
                <label class="block text-xs font-black text-slate-600">تاريخ الإرسال <span class="text-red-500">*</span></label>
                <input type="text" class="signal-date w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-black text-slate-600">وقت الإرسال <span class="text-red-500">*</span></label>
                <input type="time" class="signal-time w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-black text-slate-600">جهة إرسال الإشارة <span class="text-red-500">*</span></label>
                <select class="signal-sender w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                    <option value="" disabled selected>اختر جهة الإرسال</option>
                    @foreach ($signalAuthorities as $auth)
                        <option value="{{ $auth->ID }}">{{ $auth->SIGNAL_NAME }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
                <label class="block text-xs font-black text-slate-600">نوع الإشارة <span class="text-red-500">*</span></label>
                <div class="flex gap-6 py-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" class="signal-type w-5 h-5 accent-accent" value="إشارة لاسلكية" checked>
                        <span class="font-bold">إشارة لاسلكية</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" class="signal-type w-5 h-5 accent-accent" value="رصد مرئي">
                        <span class="font-bold">رصد مرئي</span>
                    </label>
                </div>
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-black text-slate-600">مضمون الإشارة</label>
                <select class="signal-content w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                    <option value="" disabled selected>اختر المضمون</option>
                    @foreach ($signalContents as $content)
                        <option value="{{ $content }}">{{ $content }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="space-y-1">
            <label class="block text-xs font-black text-slate-600">موضوع الإشارة</label>
            <textarea class="signal-subject w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none" rows="3"></textarea>
        </div>

        <div class="space-y-2">
            <label class="block text-xs font-black text-slate-600">جهات استقبال الإشارة</label>

            @if (count($signalAuthorityGroups))
                <div class="authority-groups-box rounded-xl border border-accent/30 bg-accent/5 p-3 space-y-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-black text-primary">
                            <i class="fas fa-layer-group text-accent"></i> مجموعات جاهزة:
                        </span>
                        <span class="text-[11px] text-slate-500 font-bold">اضغط على المجموعة لتحديد كل جهاتها بنفس الحالة</span>
                    </div>
                    <div class="authority-groups flex flex-wrap gap-2">
                        @foreach ($signalAuthorityGroups as $group)
                            <button type="button"
                                class="group-toggle flex items-center gap-2 bg-white px-3 py-2 rounded-lg border-2 border-slate-200 font-bold text-xs transition-all"
                                data-members="{{ json_encode($group['members']) }}" data-group-state=""
                                title="{{ count($group['members']) }} جهة">
                                <i class="fas fa-layer-group text-accent"></i>
                                <span>{{ $group['name'] }}</span>
                                <span class="group-count px-1.5 py-0.5 rounded-md bg-slate-100 text-[10px] text-slate-500 font-black">{{ count($group['members']) }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <input type="text" placeholder="بحث..." class="authority-search w-full px-3 py-2 rounded-lg border border-slate-200 text-sm mb-2">
            <div class="authority-list flex flex-wrap gap-3 max-h-52 overflow-y-auto p-1">
                @foreach ($signalAuthorities as $auth)
                    <button type="button"
                        class="authority-toggle flex items-center gap-2 bg-white px-4 py-2 rounded-lg border-2 border-slate-200 font-bold text-sm transition-all"
                        data-name="{{ $auth->SIGNAL_NAME }}" data-state="">
                        <span class="state-icon w-5 h-5 rounded flex items-center justify-center border-2 border-slate-300">
                            <i class="fas fa-check text-white text-xs hidden"></i>
                            <i class="fas fa-times text-white text-xs hidden"></i>
                        </span>
                        <span class="authority-name">{{ $auth->SIGNAL_NAME }}</span>
                    </button>
                @endforeach
            </div>
            <p class="text-xs text-slate-400">اضغط على الجهة لتدوير الحالة: بدون تحديد ← تم الإرسال ← لم يتم الإرسال/الاستلام</p>
        </div>
    </div>
</template>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>
    <script>
        let cardIndex = 0;
        const AUTHORITY_STATES = ['', 'Correct', 'X'];

        function addSignalCard() {
            const template = document.getElementById('signal-card-template');
            const clone = template.content.cloneNode(true);
            const wrapper = clone.querySelector('.signal-card');
            const index = cardIndex++;

            const now = new Date();
            const today = now.toISOString().slice(0, 10);
            const nowTime = now.toTimeString().slice(0, 5);

            const dateInput = wrapper.querySelector('.signal-date');
            const timeInput = wrapper.querySelector('.signal-time');
            const senderInput = wrapper.querySelector('.signal-sender');
            const contentInput = wrapper.querySelector('.signal-content');
            const subjectInput = wrapper.querySelector('.signal-subject');
            const typeInputs = wrapper.querySelectorAll('.signal-type');

            dateInput.value = today;
            dateInput.name = `signals[${index}][date]`;
            timeInput.value = nowTime;
            timeInput.name = `signals[${index}][time]`;
            senderInput.name = `signals[${index}][sender]`;
            contentInput.name = `signals[${index}][content]`;
            subjectInput.name = `signals[${index}][subject]`;
            typeInputs.forEach(input => input.name = `signals[${index}][type]`);

            // خريطة اسم الجهة -> الكارت نفسه عشان الجروبات تلاقي أعضائها بالاسم
            const authorityIndex = new Map();

            // تفعيل تدوير الحالة الثلاثية لكل جهة (فاضي -> Correct -> X -> فاضي)
            wrapper.querySelectorAll('.authority-toggle').forEach(btn => {
                const authorityName = btn.dataset.name;
                authorityIndex.set(authorityName, btn);

                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = `signals[${index}][authorities][${authorityName}]`;
                hiddenInput.value = '';
                btn.appendChild(hiddenInput);

                btn.addEventListener('click', function () {
                    const current = AUTHORITY_STATES.indexOf(this.dataset.state);
                    setAuthorityState(this, AUTHORITY_STATES[(current + 1) % AUTHORITY_STATES.length]);
                    syncGroups(wrapper, authorityIndex);
                });
            });

            // الجروبات: ضغطة واحدة بتحدد كل جهات المجموعة بنفس الحالة
            wrapper.querySelectorAll('.group-toggle').forEach(groupBtn => {
                const members = JSON.parse(groupBtn.dataset.members || '[]');

                groupBtn.addEventListener('click', function () {
                    const current = AUTHORITY_STATES.indexOf(this.dataset.groupState);
                    const next = AUTHORITY_STATES[(current + 1) % AUTHORITY_STATES.length];

                    members.forEach(name => {
                        const memberBtn = authorityIndex.get(name);
                        if (memberBtn) setAuthorityState(memberBtn, next);
                    });

                    this.dataset.groupState = next;
                    syncGroups(wrapper, authorityIndex);
                });
            });

            wrapper.querySelector('.remove-card').addEventListener('click', () => wrapper.remove());

            // بحث داخل قائمة الجهات جوه الكارت ده بس
            wrapper.querySelector('.authority-search').addEventListener('input', function () {
                const term = this.value.trim().toLowerCase();
                wrapper.querySelectorAll('.authority-toggle').forEach(btn => {
                    const name = btn.dataset.name.toLowerCase();
                    btn.style.display = name.includes(term) ? '' : 'none';
                });
            });

            document.getElementById('signal-cards').appendChild(clone);

            flatpickr(dateInput, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                locale: 'ar'
            });

            syncGroups(wrapper, authorityIndex);
        }

        function setAuthorityState(btn, state) {
            btn.dataset.state = state;
            const hidden = btn.querySelector('input[type="hidden"]');
            if (hidden) hidden.value = state;
            applyAuthorityState(btn, state);
        }

        // تحديث شكل الجروب حسب حالة أعضائه: كلها فاضية / كلها Correct / كلها X / مختلطة
        function syncGroups(wrapper, authorityIndex) {
            wrapper.querySelectorAll('.group-toggle').forEach(groupBtn => {
                const members = JSON.parse(groupBtn.dataset.members || '[]');
                const states = members
                    .map(name => (authorityIndex.get(name) || { dataset: {} }).dataset.state || '');

                const all = state => states.length > 0 && states.every(s => s === state);
                const groupState = all('Correct') ? 'Correct' : all('X') ? 'X' : all('') ? '' : 'mixed';

                if (groupState !== 'mixed') groupBtn.dataset.groupState = groupState;
                applyGroupState(groupBtn, groupState);
            });
        }

        function applyGroupState(groupBtn, state) {
            groupBtn.classList.remove(
                'border-green-500', 'bg-green-50', 'text-green-700',
                'border-red-500', 'bg-red-50', 'text-red-700',
                'border-amber-500', 'bg-amber-50', 'text-amber-700'
            );

            if (state === 'Correct') {
                groupBtn.classList.add('border-green-500', 'bg-green-50', 'text-green-700');
            } else if (state === 'X') {
                groupBtn.classList.add('border-red-500', 'bg-red-50', 'text-red-700');
            } else if (state === 'mixed') {
                groupBtn.classList.add('border-amber-500', 'bg-amber-50', 'text-amber-700');
            }
        }

        function applyAuthorityState(btn, state) {
            const icon = btn.querySelector('.state-icon');
            const checkIcon = icon.querySelector('.fa-check');
            const xIcon = icon.querySelector('.fa-times');

            checkIcon.classList.add('hidden');
            xIcon.classList.add('hidden');
            btn.classList.remove('border-green-500', 'bg-green-50', 'border-red-500', 'bg-red-50');
            icon.classList.remove('bg-green-500', 'bg-red-500', 'border-slate-300');

            if (state === 'Correct') {
                btn.classList.add('border-green-500', 'bg-green-50');
                icon.classList.add('bg-green-500');
                checkIcon.classList.remove('hidden');
            } else if (state === 'X') {
                btn.classList.add('border-red-500', 'bg-red-50');
                icon.classList.add('bg-red-500');
                xIcon.classList.remove('hidden');
            } else {
                icon.classList.add('border-slate-300');
            }
        }

        // كارت واحد افتراضي أول ما الصفحة تفتح (زي الديسكتوب بالظبط)
        addSignalCard();
    </script>
@endpush