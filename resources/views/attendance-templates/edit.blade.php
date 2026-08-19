@extends('layouts.app')

@section('title', 'تعديل تمام - نظام التمامات')
@section('page-title', 'تعديل بيانات التمام')

@section('content')
    <div class="max-w-4xl mx-auto w-full">
        <a href="{{ route('attendance-templates.index') }}"
            class="inline-flex items-center gap-2 text-primary font-bold mb-4 hover:text-accent transition-colors">
            <i class="fas fa-arrow-right"></i> رجوع لقائمة التمامات
        </a>

        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="bg-primary p-6 text-white">
                <h3 class="text-2xl font-black flex items-center gap-3">
                    <i class="fas fa-clipboard-check text-accent"></i> تعديل بيانات التمام
                </h3>
            </div>

            <form method="POST" action="{{ route('attendance-templates.update', $template) }}" class="p-8 space-y-6">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-700 text-sm">
                    <i class="fas fa-info-circle"></i>
                    تنبيه: أي تغيير في الجهات المرتبطة (إضافة أو حذف) سيؤدي لبدء دورة (Cycle) جديدة تلقائيًا.
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-xs font-black text-slate-600">اسم التمام <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $template->name) }}" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-black text-slate-600">موعد التمام <span class="text-red-500">*</span></label>
                        <input type="time" name="attendance_time" value="{{ old('attendance_time', $template->attendance_time->format('H:i')) }}" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-black text-slate-600">نص التمام (Script) <span class="text-red-500">*</span></label>
                    <textarea name="script" rows="3" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">{{ old('script', $template->script) }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-xs font-black text-slate-600">عدد الجهات المطلوبة يوميًا <span class="text-red-500">*</span></label>
                        <input type="number" name="daily_entities_count" value="{{ old('daily_entities_count', $template->daily_entities_count) }}" min="1" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-black text-slate-600">الحالة</label>
                        <label class="flex items-center gap-2 py-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active)) class="w-5 h-5 accent-accent">
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
                                    @checked(in_array($entity->id, old('entity_ids', $selectedEntityIds)))
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
                        <i class="fas fa-save text-accent"></i> حفظ التعديلات
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
    </script>
@endpush
