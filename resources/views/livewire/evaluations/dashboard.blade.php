<div dir="rtl">
    @php $arabicMonths = [1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل', 5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس', 9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر']; @endphp

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-black text-primary flex items-center gap-3">
                <span class="w-11 h-11 rounded-2xl bg-accent/10 text-accent flex items-center justify-center">
                    <i class="fas fa-chart-line"></i>
                </span>
                لوحة تحكم تقييم الجهات
            </h1>
            <p class="text-sm text-slate-400 font-bold mt-1">نتائج التقييم الشهرية وترتيب الجهات</p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <select wire:model.live="month"
                class="w-36 px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none font-bold text-sm">
                @foreach (range(1, 12) as $m)
                    <option value="{{ $m }}">{{ $arabicMonths[$m] }}</option>
                @endforeach
            </select>
            <select wire:model.live="year"
                class="w-24 px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none font-bold text-sm">
                @foreach (range(now()->year - 1, now()->year) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
            <button wire:click="recalculate" wire:confirm="إعادة حساب نتائج هذا الشهر؟"
                class="bg-accent hover:bg-accent-hover text-white font-bold px-5 py-3 rounded-xl transition-all flex items-center gap-2">
                <i class="fas fa-calculator"></i> إعادة حساب الشهر
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm font-bold flex items-center gap-3">
            <i class="fas fa-check-circle text-xl"></i> {{ session('success') }}
        </div>
    @endif

    @if ($results->isEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-3xl p-12 text-center">
            <i class="fas fa-hourglass-half text-5xl text-amber-400 mb-4"></i>
            <h3 class="text-xl font-black text-amber-700 mb-2">لا توجد نتائج محسوبة لهذا الشهر بعد</h3>
            <p class="text-amber-600 text-sm">اضغط على «إعادة حساب الشهر» لتوليد النتائج وتقييم الجهات.</p>
        </div>
    @else
        {{-- كروت أفضل / أقل جهة --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            @if ($top)
                <div class="relative overflow-hidden rounded-3xl bg-green-50 p-8 shadow-sm border-2 border-green-200">
                    <div class="absolute -top-10 -left-10 w-40 h-40 bg-green-100/60 rounded-full blur-2xl"></div>
                    <div class="absolute -bottom-10 -right-10 w-28 h-28 bg-green-100/60 rounded-full"></div>
                    <div class="relative flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-green-100 text-green-700 font-black text-sm border border-green-200">
                                <i class="fas fa-trophy"></i> أعلى جهة في الشهر
                            </span>
                            <span class="w-14 h-14 rounded-2xl bg-green-100 flex items-center justify-center">
                                <i class="fas fa-crown text-2xl text-green-600"></i>
                            </span>
                        </div>
                        <h3 class="text-3xl font-black text-green-800 leading-tight">{{ $top->entity?->name ?? '—' }}</h3>
                        <div class="flex items-center gap-6 mt-2">
                            <div>
                                <p class="text-green-600/70 text-xs font-bold mb-1">نسبة الالتزام</p>
                                <p class="text-xl font-black text-green-700">{{ $top->percentage }}%</p>
                            </div>
                            <div class="border-r border-green-200 pr-6">
                                <p class="text-green-600/70 text-xs font-bold mb-1">الدرجة</p>
                                <p class="text-xl font-black text-green-700">{{ $top->grade_out_of_20 }} <span class="text-sm font-bold text-green-600/70">/ 20</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($bottom)
                <div class="relative overflow-hidden rounded-3xl bg-white p-8 shadow-sm border-2 border-slate-100">
                    <div class="absolute -bottom-10 -right-10 w-28 h-28 bg-red-50 rounded-full"></div>
                    <div class="relative flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-50 text-red-500 font-black text-sm border border-red-100">
                                <i class="fas fa-arrow-trend-down"></i> أقل جهة في الشهر
                            </span>
                            <span class="w-14 h-14 rounded-2xl bg-red-50 flex items-center justify-center">
                                <i class="fas fa-arrow-down text-2xl text-red-400"></i>
                            </span>
                        </div>
                        <h3 class="text-3xl font-black text-primary leading-tight">{{ $bottom->entity?->name ?? '—' }}</h3>
                        <div class="flex items-center gap-6 mt-2">
                            <div>
                                <p class="text-slate-400 text-xs font-bold mb-1">نسبة الالتزام</p>
                                <p class="text-xl font-black text-slate-700">{{ $bottom->percentage }}%</p>
                            </div>
                            <div class="border-r border-slate-100 pr-6">
                                <p class="text-slate-400 text-xs font-bold mb-1">الدرجة</p>
                                <p class="text-xl font-black text-slate-700">{{ $bottom->grade_out_of_20 }} <span class="text-sm font-bold text-slate-400">/ 20</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- جدول الترتيب --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right">
                    <thead class="bg-slate-50 text-slate-500 text-sm">
                        <tr>
                            <th class="px-4 py-4 font-bold">الترتيب</th>
                            <th class="px-4 py-4 font-bold">الجهة</th>
                            <th class="px-4 py-4 font-bold">مجموع النقاط</th>
                            <th class="px-4 py-4 font-bold">النسبة</th>
                            <th class="px-4 py-4 font-bold">الدرجة من 20</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($results as $row)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-4">
                                    <span class="w-8 h-8 inline-flex items-center justify-center rounded-full text-xs font-black
                                        {{ $row->rank === 1 ? 'bg-yellow-100 text-yellow-600' : ($row->rank <= 3 ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-500') }}">
                                        {{ $row->rank }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 font-bold text-primary">
                                    <span class="inline-flex items-center gap-2">
                                        <i class="fas fa-building text-accent"></i> {{ $row->entity?->name ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">{{ $row->total_score }} / {{ $row->max_possible_score }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-24 bg-slate-100 rounded-full h-2">
                                            <div class="bg-accent h-2 rounded-full" style="width: {{ min($row->percentage, 100) }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold text-slate-500">{{ $row->percentage }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="font-black text-lg {{ $row->grade_out_of_20 >= 15 ? 'text-green-600' : ($row->grade_out_of_20 >= 10 ? 'text-amber-600' : 'text-red-500') }}">
                                        {{ $row->grade_out_of_20 }}
                                    </span>
                                    <span class="text-slate-400 text-xs">/ 20</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>