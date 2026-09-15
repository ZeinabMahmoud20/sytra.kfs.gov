<div dir="rtl">
    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-black text-primary flex items-center gap-3">
                <span class="w-11 h-11 rounded-2xl bg-accent/10 text-accent flex items-center justify-center">
                    <i class="fas fa-building"></i>
                </span>
                إدارة جهات التقييم
            </h1>
            <p class="text-sm text-slate-400 font-bold mt-1">إجمالي {{ $entities->total() }} جهة</p>
        </div>

        <button wire:click="create"
            class="bg-accent hover:bg-accent-hover text-white font-bold px-5 py-3 rounded-xl transition-all flex items-center gap-2">
            <i class="fas fa-plus"></i> إضافة جهة
        </button>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm font-bold flex items-center gap-3">
            <i class="fas fa-check-circle text-xl"></i> {{ session('success') }}
        </div>
    @endif

    {{-- نموذج إضافة / تعديل جهة --}}
    @if ($showForm)
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-6">
            <div class="bg-primary p-5 text-white flex items-center gap-3">
                <i class="fas {{ $editingId ? 'fa-edit' : 'fa-plus-circle' }} text-accent text-xl"></i>
                <h3 class="font-black text-lg">{{ $editingId ? 'تعديل جهة' : 'إضافة جهة جديدة' }}</h3>
            </div>

            <form wire:submit="save" class="p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-1">
                        <label class="block text-xs font-black text-slate-600">اسم الجهة <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="name" placeholder="مثال: إدارة الدفاع المدني"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                        @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-black text-slate-600">كود الجهة <span class="text-slate-400 font-normal">(اختياري)</span></label>
                        <input type="text" wire:model="code" placeholder="مثال: 001"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                        @error('code') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="md:col-span-2 space-y-1">
                        <label class="block text-xs font-black text-slate-600">ملاحظات</label>
                        <textarea wire:model="notes" rows="3" placeholder="أي ملاحظات عن الجهة..."
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none resize-none"></textarea>
                    </div>

                    <label class="md:col-span-2 flex items-center gap-3 cursor-pointer select-none">
                        <input type="checkbox" wire:model="is_active"
                            class="w-5 h-5 rounded border-slate-300 text-accent focus:ring-accent/30">
                        <span class="text-sm font-bold text-slate-700">جهة فعّالة</span>
                    </label>
                </div>

                <div class="pt-5 border-t border-slate-100 flex items-center gap-3">
                    <button type="submit"
                        class="bg-primary text-white font-black px-6 py-3 rounded-xl border-2 border-transparent hover:border-accent shadow-lg transition-all flex items-center gap-2">
                        <i class="fas fa-save text-accent"></i> حفظ
                    </button>
                    <button type="button" wire:click="$set('showForm', false)"
                        class="bg-slate-100 text-slate-600 font-bold px-6 py-3 rounded-xl hover:bg-slate-200 transition-all">
                        إلغاء
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- جدول الجهات --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead class="bg-slate-50 text-slate-500 text-sm">
                    <tr>
                        <th class="px-4 py-4 font-bold">#</th>
                        <th class="px-4 py-4 font-bold">الاسم</th>
                        <th class="px-4 py-4 font-bold">الكود</th>
                        <th class="px-4 py-4 font-bold">الحالة</th>
                        <th class="px-4 py-4 font-bold text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($entities as $entity)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-4 text-slate-400">{{ $entity->id }}</td>
                            <td class="px-4 py-4 font-bold text-primary">{{ $entity->name }}</td>
                            <td class="px-4 py-4" dir="ltr">{{ $entity->code ?? '-' }}</td>
                            <td class="px-4 py-4">
                                <button wire:click="toggleActive({{ $entity->id }})"
                                    class="px-3 py-1.5 rounded-full text-xs font-bold transition-all
                                    {{ $entity->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-slate-200 text-slate-600 hover:bg-slate-300' }}">
                                    <i class="fas {{ $entity->is_active ? 'fa-check-circle' : 'fa-pause-circle' }} ml-1"></i>
                                    {{ $entity->is_active ? 'فعّالة' : 'موقوفة' }}
                                </button>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button wire:click="edit({{ $entity->id }})" title="تعديل"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                    <button wire:click="delete({{ $entity->id }})" wire:confirm="متأكد من حذف هذه الجهة؟" title="حذف"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400">لا توجد جهات مسجلة بعد</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $entities->links() }}</div>
</div>