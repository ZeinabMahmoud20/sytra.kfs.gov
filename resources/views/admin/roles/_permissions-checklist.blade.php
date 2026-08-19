{{--
    Partial مشترك بين صفحة إضافة دور وتعديل دور.
    المتغيرات المطلوبة:
    - $permissions: مجموعة الصلاحيات مجمّعة حسب الموديول (from controller: ->groupBy)
    - $checkedPermissions: array بأسماء الصلاحيات المفعّلة حالياً (اختياري، فاضي وقت الإضافة)
--}}
<div class="space-y-6">
    @foreach ($permissions as $module => $modulePermissions)
        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-black text-primary">{{ $module }}</h4>
                <label class="text-xs text-accent font-bold cursor-pointer select-none">
                    <input type="checkbox" class="module-toggle-all" data-module="{{ $module }}" class="ml-1">
                    تحديد الكل
                </label>
            </div>
            <div class="flex flex-wrap gap-3">
                @foreach ($modulePermissions as $permission)
                    <label class="flex items-center gap-2 cursor-pointer bg-white px-3 py-2 rounded-lg border border-slate-200 hover:border-accent">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                            class="permission-checkbox w-4 h-4 accent-accent" data-module="{{ $module }}"
                            @checked(in_array($permission->name, $checkedPermissions ?? []))>
                        <span class="text-sm font-bold">{{ $permission->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

@push('scripts')
    <script>
        document.querySelectorAll('.module-toggle-all').forEach(toggle => {
            toggle.addEventListener('change', function () {
                const module = this.dataset.module;
                document.querySelectorAll(`.permission-checkbox[data-module="${module}"]`).forEach(cb => {
                    cb.checked = this.checked;
                });
            });
        });
    </script>
@endpush
