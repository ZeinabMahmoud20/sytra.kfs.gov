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
                <input type="date" class="signal-date w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
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
                <input type="text" list="signal-contents-list" class="signal-content w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none" placeholder="اكتب أو اختر من المضامين الجاهزة">
            </div>
        </div>

        <div class="space-y-1">
            <label class="block text-xs font-black text-slate-600">موضوع الإشارة</label>
            <textarea class="signal-subject w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none" rows="3"></textarea>
        </div>

        <div class="space-y-2">
            <label class="block text-xs font-black text-slate-600">جهات استقبال الإشارة</label>
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

<datalist id="signal-contents-list">
    @foreach ($signalContents as $content)
        <option value="{{ $content }}"></option>
    @endforeach
</datalist>

@push('scripts')
    <script>
        let cardIndex = 0;

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

            // تفعيل تدوير الحالة الثلاثية لكل جهة (فاضي -> Correct -> X -> فاضي)
            wrapper.querySelectorAll('.authority-toggle').forEach(btn => {
                const authorityName = btn.dataset.name;
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = `signals[${index}][authorities][${authorityName}]`;
                hiddenInput.value = '';
                btn.appendChild(hiddenInput);

                btn.addEventListener('click', function () {
                    const states = ['', 'Correct', 'X'];
                    const current = states.indexOf(this.dataset.state);
                    const next = states[(current + 1) % states.length];
                    this.dataset.state = next;
                    hiddenInput.value = next;
                    applyAuthorityState(this, next);
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