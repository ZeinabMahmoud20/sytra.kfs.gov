@extends('layouts.app')

@section('title', 'إضافة تمام جديد - نظام التمامات')
@section('page-title', 'إضافة تمام جديد')

@section('content')
    <div class="max-w-4xl mx-auto w-full">
        <a href="{{ route('attendance-templates.index') }}"
            class="inline-flex items-center gap-2 text-primary font-bold mb-4 hover:text-accent transition-colors">
            <i class="fas fa-arrow-right"></i> رجوع لقائمة التمامات
        </a>

        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="bg-primary p-6 text-white">
                <h3 class="text-2xl font-black flex items-center gap-3">
                    <i class="fas fa-clipboard-check text-accent"></i> إضافة تمام جديد
                </h3>
            </div>

            <form method="POST" action="{{ route('attendance-templates.store') }}" class="p-8 space-y-6">
                @csrf

                @if ($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-xs font-black text-slate-600">اسم التمام <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none"
                            placeholder="مثال: تمام الصباح">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-black text-slate-600">موعد التمام <span class="text-red-500">*</span></label>
                        <input type="time" name="attendance_time" value="{{ old('attendance_time') }}" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-black text-slate-600">تكرار التمام <span class="text-red-500">*</span></label>
                    <select name="frequency" id="frequency-select"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                        <option value="daily" @selected(old('frequency') === 'daily')>يومي</option>
                        <option value="twice_daily" @selected(old('frequency') === 'twice_daily')>مرتين في اليوم</option>
                        <option value="weekly" @selected(old('frequency') === 'weekly')>أسبوعي</option>
                        <option value="monthly" @selected(old('frequency') === 'monthly')>شهري</option>
                        <option value="custom_dates" @selected(old('frequency') === 'custom_dates')>بتاريخ دوري</option>
                    </select>
                </div>

                {{-- موعد التمام الثاني (يظهر لما يختار مرتين في اليوم) --}}
                <div id="second-time-group" class="space-y-1 hidden">
                    <label class="block text-xs font-black text-slate-600">موعد التمام الثاني <span class="text-red-500">*</span></label>
                    <input type="time" name="second_attendance_time" value="{{ old('second_attendance_time') }}"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                </div>

                {{-- أيام الأسبوع (يظهر لما يختار أسبوعي) --}}
                <div id="weekly-group" class="space-y-2 hidden">
                    <label class="block text-xs font-black text-slate-600">أيام التمام <span class="text-red-500">*</span></label>
                    <div class="flex flex-wrap gap-3">
                        @php
                            $weekDays = [
                                0 => 'الأحد', 1 => 'الإثنين', 2 => 'الثلاثاء',
                                3 => 'الأربعاء', 4 => 'الخميس', 5 => 'الجمعة', 6 => 'السبت'
                            ];
                            $selectedDays = old('frequency_config.days_of_week', []);
                        @endphp
                        @foreach ($weekDays as $value => $label)
                            <label class="flex items-center gap-2 bg-slate-50 px-4 py-2 rounded-lg border-2 border-slate-200 font-bold text-sm cursor-pointer has-[:checked]:border-accent has-[:checked]:bg-accent/10">
                                <input type="checkbox" name="frequency_config[days_of_week][]" value="{{ $value }}"
                                    @checked(in_array($value, $selectedDays))
                                    class="w-4 h-4 accent-accent">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- يوم الشهر (يظهر لما يختار شهري) --}}
                <div id="monthly-group" class="space-y-1 hidden">
                    <label class="block text-xs font-black text-slate-600">يوم التمام في الشهر <span class="text-red-500">*</span></label>
                    <input type="number" name="frequency_config[day_of_month]" value="{{ old('frequency_config.day_of_month') }}" min="1" max="31"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none"
                        placeholder="مثال: 15">
                    <p class="text-xs text-slate-400">أدخل رقم يوم من 1 إلى 31</p>
                </div>

                {{-- تواريخ محددة (يظهر لما يختار بتاريخ دوري) --}}
                <div id="custom-dates-group" class="space-y-1 hidden">
                    <label class="block text-xs font-black text-slate-600">التواريخ المحددة <span class="text-red-500">*</span></label>
                    <input type="date" id="custom-date-input"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                    <div id="selected-dates" class="flex flex-wrap gap-2 mt-2">
                        @foreach (old('frequency_config.custom_dates', []) as $date)
                            <span class="selected-date-tag inline-flex items-center gap-1 bg-accent/10 text-accent px-3 py-1 rounded-lg text-sm font-bold">
                                {{ $date }}
                                <button type="button" onclick="this.parentElement.remove()" class="text-accent hover:text-red-500">&times;</button>
                                <input type="hidden" name="frequency_config[custom_dates][]" value="{{ $date }}">
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-black text-slate-600">نص التمام (Script) <span class="text-red-500">*</span></label>
                    <textarea name="script" rows="3" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none"
                        placeholder="النص اللي هيقوله الموظف للجهة">{{ old('script') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-xs font-black text-slate-600">عدد الجهات المطلوبة يوميًا <span class="text-red-500">*</span></label>
                        <input type="number" name="daily_entities_count" value="{{ old('daily_entities_count') }}" min="1" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-black text-slate-600">الحالة</label>
                        <label class="flex items-center gap-2 py-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="w-5 h-5 accent-accent">
                            <span class="font-bold">تمام نشط</span>
                        </label>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-black text-slate-600">الجهات المرتبطة بالتمام <span class="text-red-500">*</span></label>
                    <input type="text" placeholder="بحث عن جهة..." id="entity-search"
                        class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm mb-2">
                    <div id="entity-list" class="flex flex-wrap gap-3 max-h-64 overflow-y-auto p-3 border border-slate-200 rounded-xl">
                        @foreach ($entities as $entity)
                            <label class="entity-item flex items-center gap-2 bg-slate-50 px-4 py-2 rounded-lg border-2 border-slate-200 font-bold text-sm cursor-pointer has-[:checked]:border-accent has-[:checked]:bg-accent/10">
                                <input type="checkbox" name="entity_ids[]" value="{{ $entity->id }}"
                                    @checked(in_array($entity->id, old('entity_ids', [])))
                                    class="w-4 h-4 accent-accent">
                                <span class="entity-name">{{ $entity->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p id="selected-count" class="text-xs text-slate-400"></p>
                </div>

                <div class="pt-6 border-t border-slate-100">
                    <button type="submit"
                        class="w-full bg-primary text-white font-black py-4 rounded-2xl border-2 border-transparent hover:border-accent shadow-lg text-lg transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-save text-accent"></i> حفظ التمام
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('entity-search').addEventListener('input', function () {
            const term = this.value.trim().toLowerCase();
            document.querySelectorAll('.entity-item').forEach(item => {
                const name = item.querySelector('.entity-name').textContent.toLowerCase();
                item.style.display = name.includes(term) ? '' : 'none';
            });
        });

        function updateSelectedCount() {
            const count = document.querySelectorAll('#entity-list input[type="checkbox"]:checked').length;
            document.getElementById('selected-count').textContent = `تم اختيار ${count} جهة`;
        }

        document.querySelectorAll('#entity-list input[type="checkbox"]').forEach(input => {
            input.addEventListener('change', updateSelectedCount);
        });

        updateSelectedCount();

        // frequency toggle
        const freqSelect = document.getElementById('frequency-select');
        const secondTimeGroup = document.getElementById('second-time-group');
        const weeklyGroup = document.getElementById('weekly-group');
        const monthlyGroup = document.getElementById('monthly-group');
        const customDatesGroup = document.getElementById('custom-dates-group');

        function toggleFrequencyFields() {
            const val = freqSelect.value;
            secondTimeGroup.classList.toggle('hidden', val !== 'twice_daily');
            weeklyGroup.classList.toggle('hidden', val !== 'weekly');
            monthlyGroup.classList.toggle('hidden', val !== 'monthly');
            customDatesGroup.classList.toggle('hidden', val !== 'custom_dates');
        }

        freqSelect.addEventListener('change', toggleFrequencyFields);
        toggleFrequencyFields();

        // custom dates picker
        const dateInput = document.getElementById('custom-date-input');
        const selectedDatesContainer = document.getElementById('selected-dates');

        dateInput.addEventListener('change', function () {
            const date = this.value;
            if (!date) return;

            const existing = selectedDatesContainer.querySelectorAll('input[type="hidden"]');
            for (let i = 0; i < existing.length; i++) {
                if (existing[i].value === date) {
                    this.value = '';
                    return;
                }
            }

            const tag = document.createElement('span');
            tag.className = 'selected-date-tag inline-flex items-center gap-1 bg-accent/10 text-accent px-3 py-1 rounded-lg text-sm font-bold';
            tag.innerHTML = `${date} <button type="button" onclick="this.parentElement.remove()" class="text-accent hover:text-red-500">&times;</button><input type="hidden" name="frequency_config[custom_dates][]" value="${date}">`;
            selectedDatesContainer.appendChild(tag);
            this.value = '';
        });

        // clear hidden fields before submit so they don't fail validation
        document.querySelector('form').addEventListener('submit', function () {
            const val = freqSelect.value;
            if (val !== 'twice_daily') secondTimeGroup.querySelectorAll('input').forEach(el => el.disabled = true);
            if (val !== 'weekly') weeklyGroup.querySelectorAll('input').forEach(el => el.disabled = true);
            if (val !== 'monthly') monthlyGroup.querySelector('input').disabled = true;
            if (val !== 'custom_dates') customDatesGroup.querySelectorAll('input[type="hidden"]').forEach(el => el.disabled = true);
        });
    </script>
@endpush