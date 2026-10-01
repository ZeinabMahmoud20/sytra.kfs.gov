{{-- حقول نموذج مجموعة جهات الإشارة - مشترك بين الإضافة والتعديل --}}
<div class="space-y-1">
    <label class="block text-xs font-black text-slate-600">اسم المجموعة <span class="text-red-500">*</span></label>
    <input type="text" name="GROUP_NAME" value="{{ old('GROUP_NAME', $groupName ?? '') }}" required
        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none"
        placeholder="مثال: جهات الاستجابة للطوارئ">
</div>

<div class="space-y-2">
    <label class="block text-xs font-black text-slate-600">جهات الإشارة الأعضاء في المجموعة</label>

    <input type="text" id="member-search-{{ $formId }}" placeholder="بحث عن جهة..." data-target="member-list-{{ $formId }}"
        class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm mb-2">

    <div id="member-list-{{ $formId }}" class="flex flex-wrap gap-2 max-h-64 overflow-y-auto p-2 border border-slate-200 rounded-xl bg-slate-50">
        @forelse ($authorities as $authority)
            <label class="member-toggle flex items-center gap-2 bg-white px-4 py-2 rounded-lg border-2 border-slate-200 font-bold text-sm cursor-pointer transition-all hover:border-accent/60"
                data-name="{{ $authority->SIGNAL_NAME }}">
                <input type="checkbox" name="authorities[]" value="{{ $authority->ID }}" class="w-4 h-4 accent-accent"
                    @checked(in_array($authority->ID, $selectedAuthorities))>
                <span class="authority-name">{{ $authority->SIGNAL_NAME }}</span>
            </label>
        @empty
            <span class="text-slate-400 text-sm p-2">لا توجد جهات إشارة مسجلة - أضف جهات من صفحة جهات الإشارة</span>
        @endforelse
    </div>

    <p class="text-xs text-slate-400">
        <i class="fas fa-circle-info"></i>
        يتم تحديد كل الجهات دفعة واحدة عند تسجيل الإشارة.
    </p>
</div>

@push('scripts')
    <script>
        (function () {
            const search = document.getElementById('member-search-{{ $formId }}');
            if (!search) return;
            const list = document.getElementById('member-list-{{ $formId }}');
            search.addEventListener('input', function () {
                const term = this.value.trim().toLowerCase();
                list.querySelectorAll('.member-toggle').forEach(toggle => {
                    toggle.style.display = toggle.dataset.name.toLowerCase().includes(term) ? '' : 'none';
                });
            });
        })();
    </script>
@endpush
