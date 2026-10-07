<div dir="rtl" wire:poll.120s>
    <style>[x-cloak] { display: none !important; }</style>

    @php
        $cardBase = 'relative rounded-2xl border-2 p-3 text-center transition-all';
        $statusStyles = [
            'friday'   => 'bg-slate-50 border-slate-200 text-slate-400',
            'future'   => 'bg-white border-slate-100 text-slate-300',
            'complete' => 'bg-green-50 border-green-200 text-green-700',
            'partial'  => 'bg-amber-50 border-amber-300 text-amber-700',
            'missing'  => 'bg-red-50 border-red-200 text-red-600',
        ];
        $barStyles = [
            'complete' => 'bg-green-500',
            'partial'  => 'bg-amber-500',
            'missing'  => 'bg-red-400',
        ];
    @endphp

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-black text-primary flex items-center gap-3">
                <span class="w-11 h-11 rounded-2xl bg-accent/10 text-accent flex items-center justify-center">
                    <i class="fas fa-calendar-days"></i>
                </span>
                نسبة تسجيل التقييم اليومي
            </h1>
            <p class="text-sm text-slate-400 font-bold mt-1">
                شهر {{ $monthName }} {{ $year }} — من يوم 1 إلى يوم {{ $daysInMonth }} (اليوم {{ $today }})
            </p>
        </div>

        <button wire:click="$refresh"
            class="bg-accent hover:bg-accent-hover text-white font-bold px-5 py-3 rounded-xl transition-all flex items-center gap-2">
            <i class="fas fa-rotate"></i> تحديث
        </button>
    </div>

    @if ($entitiesCount === 0)
        <div class="bg-amber-50 border border-amber-200 rounded-3xl p-12 text-center">
            <i class="fas fa-building text-5xl text-amber-400 mb-4"></i>
            <h3 class="text-xl font-black text-amber-700 mb-2">لا توجد جهات تقييم نشطة</h3>
            <p class="text-amber-600 text-sm">أضف الجهات من «إدارة الجهات» لتظهر نسب التقييم اليومية هنا.</p>
        </div>
    @else

        {{-- تنبيه: جهات بدون تقييم --}}
        @if (count($incomplete) > 0)
            <div x-data="{ open: false }" class="bg-red-50 border-2 border-red-200 rounded-3xl p-6 mb-6">
                <button type="button" x-on:click="open = !open" class="w-full flex items-center justify-between gap-4 text-right">
                    <span class="flex items-center gap-4">
                        <span class="w-12 h-12 rounded-2xl bg-red-100 text-red-500 flex items-center justify-center shrink-0">
                            <i class="fas fa-triangle-exclamation text-xl"></i>
                        </span>
                        <span class="text-right">
                            <span class="block font-black text-red-700 text-lg">
                                تنبيه: يوجد جهات بدون تقييم في {{ count($incomplete) }} يوم هذا الشهر
                            </span>
                            <span class="block text-sm font-bold text-red-500 mt-1">
                                اضغط لعرض الأيام والجهات الناقصة — لم يتم تسجيل تقييم لجميع الجهات في هذه الأيام
                            </span>
                        </span>
                    </span>
                    <i class="fas fa-chevron-down text-red-400 transition-transform duration-300" x-bind:class="open ? 'rotate-180' : ''"></i>
                </button>

                <div x-show="open" x-cloak class="mt-5 space-y-3 border-t border-red-200 pt-5">
                    @foreach ($incomplete as $item)
                        <div class="bg-white rounded-2xl border border-red-100 p-4 flex flex-wrap items-center gap-x-4 gap-y-2">
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-red-100 text-red-700 font-black text-xs border border-red-200 shrink-0">
                                <i class="fas fa-calendar-xmark"></i>
                                يوم {{ $item['day'] }} ({{ $item['day_name'] }})
                                @if ($item['is_today'])
                                    <span class="mr-1 px-2 py-0.5 rounded-full bg-accent text-white text-[10px]">اليوم</span>
                                @endif
                            </span>
                            <span class="font-bold text-sm text-slate-600 shrink-0">
                                تم تقييم {{ $item['rated'] }}/{{ $entitiesCount }} جهة ({{ $item['percentage'] }}%)
                            </span>
                            <span class="text-xs font-bold text-red-500">
                                بدون تقييم: {{ implode('، ', $item['missing']) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif ($workingDays > 0)
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-2xl text-green-700 text-sm font-bold flex items-center gap-3">
                <i class="fas fa-circle-check text-xl"></i>
                جميع الجهات تم تقييمها في كل أيام العمل حتى اليوم — لا توجد جهات بدون تقييم.
            </div>
        @else
            <div class="mb-6 p-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-500 text-sm font-bold flex items-center gap-3">
                <i class="fas fa-info-circle text-xl"></i>
                لم تبدأ أيام العمل في هذا الشهر بعد.
            </div>
        @endif

        {{-- كروت الملخص --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                <p class="text-slate-400 text-xs font-bold mb-2">نسبة التقييم الشهرية حتى الآن</p>
                <p class="text-3xl font-black {{ ($monthPercentage ?? 0) >= 100 ? 'text-green-600' : (($monthPercentage ?? 0) > 0 ? 'text-amber-600' : 'text-red-500') }}">
                    {{ $monthPercentage !== null ? $monthPercentage . '%' : '—' }}
                </p>
                <div class="w-full bg-slate-100 rounded-full h-2 mt-3">
                    <div class="h-2 rounded-full {{ ($monthPercentage ?? 0) >= 100 ? 'bg-green-500' : 'bg-accent' }}"
                        style="width: {{ min($monthPercentage ?? 0, 100) }}%"></div>
                </div>
                <p class="text-[11px] font-bold text-slate-400 mt-2">{{ $ratedTotal }} / {{ $expectedTotal }} تقييم مسجّل</p>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                <p class="text-slate-400 text-xs font-bold mb-2">أيام مكتملة التقييم</p>
                <p class="text-3xl font-black text-green-600">{{ $completeDays }} <span class="text-base text-slate-400">/ {{ $workingDays }}</span></p>
                <p class="text-[11px] font-bold text-slate-400 mt-3">أيام العمل المنقضية في الشهر</p>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                <p class="text-slate-400 text-xs font-bold mb-2">أيام بها جهات بدون تقييم</p>
                <p class="text-3xl font-black {{ count($incomplete) > 0 ? 'text-red-500' : 'text-green-600' }}">
                    {{ count($incomplete) }}
                </p>
                <p class="text-[11px] font-bold text-slate-400 mt-3">
                    @if (count($incomplete) > 0)
                        <i class="fas fa-triangle-exclamation text-red-400"></i> تحتاج إلى إجراء
                    @else
                        <i class="fas fa-check text-green-500"></i> لا يوجد تنبيه
                    @endif
                </p>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                <p class="text-slate-400 text-xs font-bold mb-2">عدد الجهات النشطة</p>
                <p class="text-3xl font-black text-primary">{{ $entitiesCount }}</p>
                <p class="text-[11px] font-bold text-slate-400 mt-3">المتوقّع تقييمها كل يوم عمل</p>
            </div>
        </div>

        {{-- شبكة أيام الشهر --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5 sm:p-6 mb-6">
            <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
                <h2 class="text-lg font-black text-primary flex items-center gap-2">
                    <i class="fas fa-list-check text-accent"></i>
                    نسبة تقييم كل يوم في الشهر
                </h2>
                <div class="flex items-center gap-4 text-[11px] font-bold text-slate-500 flex-wrap">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-green-500"></span> مكتمل 100%</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-500"></span> جزئي</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-red-400"></span> بدون تقييم</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-slate-300"></span> عطلة / قادم</span>
                </div>
            </div>

            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-7 gap-3">
                @foreach ($days as $d)
                    <div class="{{ $cardBase }} {{ $statusStyles[$d['status']] }}
                        {{ $d['is_today'] ? 'ring-2 ring-accent ring-offset-2' : '' }}"
                        @if (!empty($d['missing'])) title="بدون تقييم: {{ implode('، ', $d['missing']) }}" @endif>

                        @if ($d['is_today'])
                            <span class="absolute -top-2.5 right-1/2 translate-x-1/2 px-2 py-0.5 rounded-full bg-accent text-white text-[9px] font-black shadow">اليوم</span>
                        @endif

                        <p class="text-2xl font-black leading-none">{{ $d['day'] }}</p>
                        <p class="text-[10px] font-bold mt-1 opacity-80">{{ $d['day_name'] }}</p>

                        @if ($d['status'] === 'friday')
                            <p class="text-[11px] font-black mt-2">
                                <i class="fas fa-moon"></i> عطلة
                            </p>
                        @elseif ($d['status'] === 'future')
                            <p class="text-[11px] font-black mt-2">قادم</p>
                        @else
                            <p class="text-xl font-black mt-2">{{ $d['percentage'] }}%</p>
                            <div class="w-full bg-black/10 rounded-full h-1.5 mt-1.5">
                                <div class="h-1.5 rounded-full {{ $barStyles[$d['status']] }}" style="width: {{ $d['percentage'] }}%"></div>
                            </div>
                            <p class="text-[10px] font-bold mt-1.5">{{ $d['rated'] }}/{{ $d['expected'] }} جهة</p>
                            @if (count($d['missing']) > 0)
                                <p class="text-[10px] font-black mt-1">
                                    <i class="fas fa-triangle-exclamation"></i> {{ count($d['missing']) }} ناقصة
                                </p>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
