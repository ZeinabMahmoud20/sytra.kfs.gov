<div dir="rtl">
    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-black text-primary flex items-center gap-3">
                <span class="w-11 h-11 rounded-2xl bg-accent/10 text-accent flex items-center justify-center">
                    <i class="fas fa-clipboard-check"></i>
                </span>
                التقييم اليومي للجهات
            </h1>
            <p class="text-sm text-slate-400 font-bold mt-1">سجّل مدى استجابة كل جهة لشبكة الطوارئ</p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            @if (!$editingScript)
                <button wire:click="startEditingScript"
                    class="bg-white border-2 border-primary text-primary hover:bg-primary hover:text-white font-bold px-5 py-3 rounded-xl transition-all flex items-center gap-2">
                    <i class="fas fa-scroll"></i> {{ $script ? 'تعديل النص اليومي' : 'إضافة نص اليوم' }}
                </button>
            @endif

            <div class="bg-white rounded-2xl border border-slate-200 px-4 py-2.5 flex items-center gap-3 shadow-sm">
                <i class="fas fa-calendar-day text-accent"></i>
                <input type="text" wire:ignore id="eval-date" value="{{ $date }}"
                    class="bg-transparent outline-none font-bold text-sm text-slate-700" readonly>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm font-bold flex items-center gap-3">
            <i class="fas fa-check-circle text-xl"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm font-bold flex items-center gap-3">
            <i class="fas fa-exclamation-circle text-xl"></i> {{ session('error') }}
        </div>
    @endif

    {{-- النص اليومي --}}
    @if ($editingScript)
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mb-6">
            <h3 class="font-black text-primary flex items-center gap-2 mb-4">
                <i class="fas fa-scroll text-accent"></i> نص التقييم اليومي
            </h3>
            <div class="space-y-3">
                <textarea wire:model="script" rows="4"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none resize-none"
                    placeholder="اكتب النص اللي هيظهر فوق جدول التقييم..."></textarea>
                @error('script') <span class="text-red-500 text-xs block">{{ $message }}</span> @enderror
                <div class="flex items-center gap-3">
                    <button wire:click="saveScript"
                        class="bg-primary text-white font-black px-5 py-2.5 rounded-xl border-2 border-transparent hover:border-accent shadow-lg transition-all flex items-center gap-2">
                        <i class="fas fa-save text-accent"></i> حفظ النص
                    </button>
                    <button wire:click="cancelEditingScript"
                        class="bg-slate-100 text-slate-600 font-bold px-5 py-2.5 rounded-xl hover:bg-slate-200 transition-all">
                        إلغاء
                    </button>
                </div>
            </div>
        </div>
    @elseif ($script)
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mb-6">
            <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
                <h3 class="font-black text-primary flex items-center gap-2">
                    <i class="fas fa-scroll text-accent"></i> نص التقييم اليومي
                </h3>
                <button wire:click="startEditingScript"
                    class="text-accent font-bold text-sm hover:text-accent-hover transition-colors">
                    <i class="fas fa-pen ml-1"></i> تعديل
                </button>
            </div>
            <p class="text-slate-600 leading-8 whitespace-pre-wrap">{{ $script }}</p>
        </div>
    @endif

    {{-- يوم الجمعة --}}
    @if ($isFriday)
        <div class="bg-amber-50 border border-amber-200 rounded-3xl p-12 text-center">
            <i class="fas fa-moon text-5xl text-amber-400 mb-4"></i>
            <h3 class="text-xl font-black text-amber-700 mb-2">مفيش تقييم يوم الجمعة 🙂</h3>
            <p class="text-amber-600 text-sm">يوم الجمعة إجازة رسمية، التقييم بيتوقف.</p>
        </div>
    @else
        {{-- جدول التقييم --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right">
                    <thead class="bg-slate-50 text-slate-500 text-sm">
                        <tr>
                            <th class="px-4 py-4 font-bold">#</th>
                            <th class="px-4 py-4 font-bold">الجهة</th>
                            <th class="px-4 py-4 font-bold">التقييم</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($entities as $entity)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-4 text-slate-400">{{ $loop->iteration }}</td>
                                <td class="px-4 py-4 font-bold text-primary">
                                    <span class="inline-flex items-center gap-2">
                                        <i class="fas fa-building text-accent"></i> {{ $entity->name }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    @if (isset($responses[$entity->id]))
                                        <span class="inline-flex items-center gap-2 px-4 py-2 bg-green-50 text-green-700 rounded-full text-sm font-bold border border-green-200">
                                            <i class="fas fa-check-circle"></i>
                                            تم التقييم: {{ $responseTypes[$responses[$entity->id]] }}
                                        </span>
                                    @else
                                        <div class="flex gap-2 flex-wrap">
                                            @foreach ($responseTypes as $key => $label)
                                                <button wire:click="save({{ $entity->id }}, '{{ $key }}')"
                                                    class="px-4 py-2 rounded-xl text-sm font-bold border border-slate-200 bg-white text-slate-600 hover:border-accent hover:text-accent hover:bg-accent/5 transition-all">
                                                    {{ $label }}
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center text-slate-400">لا توجد جهات فعّالة للتقييم</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>