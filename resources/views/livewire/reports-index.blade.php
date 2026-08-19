<div>

    {{-- ================= رسالة نجاح ================= --}}
    @if (session('success'))
        <div class="mb-4 bg-green-50 text-green-700 border border-green-200 rounded-xl px-4 py-3 font-bold">
            {{ session('success') }}
        </div>
    @endif

    {{-- ================= لوحة الفلاتر ================= --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">

            {{-- الرقم القومي --}}
            <div>
                <label class="block text-sm font-bold text-slate-500 mb-1">الرقم القومي</label>
                <input type="text" wire:model.live.debounce.400ms="nationalId"
                    class="w-full rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-right">
            </div>

            {{-- المركز --}}
            <div>
                <label class="block text-sm font-bold text-slate-500 mb-1">المركز</label>
                <select wire:model.live="centerId" class="w-full rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-right">
                    <option value="">اختر المركز</option>
                    @foreach ($centers as $center)
                        <option value="{{ $center->id }}">{{ $center->CENTER_NAME }}</option>
                    @endforeach
                </select>
            </div>

            {{-- المدينة (مترابطة مع المركز) --}}
            <div>
                <label class="block text-sm font-bold text-slate-500 mb-1">المدينة</label>
                <select wire:model.live="cityId" @if(!$centerId) disabled @endif
                    class="w-full rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-right disabled:bg-slate-50 disabled:text-slate-300">
                    <option value="">اختر المدينة</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city->id }}">{{ $city->CITY_NAME }}</option>
                    @endforeach
                </select>
            </div>

            {{-- القرية (مترابطة مع المدينة) --}}
            <div>
                <label class="block text-sm font-bold text-slate-500 mb-1">القرية</label>
                <select wire:model.live="villageId" @if(!$cityId) disabled @endif
                    class="w-full rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-right disabled:bg-slate-50 disabled:text-slate-300">
                    <option value="">اختر القرية</option>
                    @foreach ($villages as $village)
                        <option value="{{ $village->id }}">{{ $village->VILLAGE_NAME }}</option>
                    @endforeach
                </select>
            </div>

            {{-- جهة البلاغ --}}
            <div>
                <label class="block text-sm font-bold text-slate-500 mb-1">جهة البلاغ</label>
                <select wire:model.live="reportingEntityId" class="w-full rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-right">
                    <option value="">اختر جهة البلاغ</option>
                    @foreach ($reportingEntities as $entity)
                        <option value="{{ $entity->id }}">{{ $entity->ENTITY_NAME }}</option>
                    @endforeach
                </select>
            </div>

            {{-- نوع البلاغ --}}
            <div>
                <label class="block text-sm font-bold text-slate-500 mb-1">نوع البلاغ</label>
                <select wire:model.live="reportingTypeId" class="w-full rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-right">
                    <option value="">اختر نوع البلاغ</option>
                    @foreach ($reportingTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->REPORT_SORT }}</option>
                    @endforeach
                </select>
            </div>

            {{-- حالة البلاغ --}}
            <div>
                <label class="block text-sm font-bold text-slate-500 mb-1">حالة البلاغ</label>
                <select wire:model.live="status" class="w-full rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-right">
                    <option value="">الكل</option>
                    <option value="قيد المعالجة">قيد المعالجة</option>
                    <option value="تم التنفيذ">تم التنفيذ</option>
                    <option value="تم استلام">تم استلام</option>
                </select>
            </div>

            {{-- من تاريخ --}}
            <div>
                <label class="block text-sm font-bold text-slate-500 mb-1">من تاريخ</label>
                <input type="date" wire:model.live="dateFrom"
                    class="w-full rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-right">
            </div>

            {{-- الي تاريخ --}}
            <div>
                <label class="block text-sm font-bold text-slate-500 mb-1">الي تاريخ</label>
                <input type="date" wire:model.live="dateTo"
                    class="w-full rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-right">
            </div>

            {{-- فلترة بالوقت --}}
            <div class="flex items-center gap-2 mt-6">
                <input type="checkbox" wire:model.live="filterByTime" id="filterByTime"
                    class="rounded border-slate-300 text-accent focus:ring-accent">
                <label for="filterByTime" class="text-sm font-bold text-slate-500">فلترة بالوقت</label>
            </div>

            {{-- من وقت --}}
            <div>
                <label class="block text-sm font-bold text-slate-500 mb-1">من وقت</label>
                <input type="time" wire:model.live="timeFrom" @if(!$filterByTime) disabled @endif
                    class="w-full rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-right disabled:bg-slate-50 disabled:text-slate-300">
            </div>

            {{-- الي وقت --}}
            <div>
                <label class="block text-sm font-bold text-slate-500 mb-1">الي وقت</label>
                <input type="time" wire:model.live="timeTo" @if(!$filterByTime) disabled @endif
                    class="w-full rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-right disabled:bg-slate-50 disabled:text-slate-300">
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-4">
            <button wire:click="resetFilters" type="button"
                class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold px-5 py-2.5 rounded-xl transition-all flex items-center gap-2">
                <i class="fas fa-rotate-left"></i> تفريغ الفلتر
            </button>
        </div>
    </div>

    {{-- ================= الجدول ================= --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden relative">

        {{-- مؤشر تحميل أثناء الفلترة --}}
        <div wire:loading class="absolute inset-0 bg-white/60 flex items-center justify-center z-10">
            <i class="fas fa-spinner fa-spin text-primary text-2xl"></i>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead class="bg-slate-50 text-slate-500 text-sm">
                    <tr>
                        <th class="px-6 py-4 font-bold">رقم البلاغ</th>
                        <th class="px-6 py-4 font-bold">نوع البلاغ</th>
                        <th class="px-6 py-4 font-bold">المركز</th>
                        <th class="px-6 py-4 font-bold">الموقع</th>
                        <th class="px-6 py-4 font-bold">التاريخ</th>
                        <th class="px-6 py-4 font-bold text-center">الحالة</th>
                        <th class="px-6 py-4 font-bold text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($reports as $report)
                        <tr class="hover:bg-slate-50 transition-colors" wire:key="report-{{ $report->id }}">
                            <td class="px-6 py-4 font-bold text-primary">{{ $report->REPORT_REGISTER_NUMBER }}</td>
                            <td class="px-6 py-4 font-semibold">{{ $report->reportingType->REPORT_SORT ?? '-' }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ $report->center->CENTER_NAME ?? '-' }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ $report->PLACE_Accident }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ $report->REPORT_START_DATE }}</td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusStyles = [
                                        'تم التنفيذ' => 'bg-green-100 text-green-600',
                                        'قيد المعالجة' => 'bg-yellow-100 text-yellow-600',
                                        'تم استلام' => 'bg-rose-100 text-rose-500',
                                    ];
                                    $style = $statusStyles[$report->REQUEST_STATUS] ?? 'bg-slate-100 text-slate-500';
                                @endphp
                                <span class="px-3 py-1 {{ $style }} rounded-full text-sm font-black">
                                    {{ $report->REQUEST_STATUS }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-1">
                                    {{-- عرض --}}
                                    <a href="{{ route('reports.show', $report->id) }}"
                                        title="عرض البلاغ"
                                        class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-400 hover:bg-primary/10 hover:text-primary transition-all">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    {{-- تعديل --}}
                                    <a href="{{ route('reports.edit', $report->id) }}"
                                        title="تعديل البلاغ"
                                        class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-400 hover:bg-accent/10 hover:text-accent transition-all">
                                        <i class="fas fa-pen"></i>
                                    </a>

                                    {{-- اضافة مرفقات --}}
                                    <a href="{{ route('reports.attachments.create', $report->id) }}"
                                        title="اضافة مرفقات"
                                        class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-400 hover:bg-blue-100 hover:text-blue-500 transition-all">
                                        <i class="fas fa-paperclip"></i>
                                    </a>

                                    {{-- عرض مرفقات --}}
                                    <a href="{{ route('reports.attachments.index', $report->id) }}"
                                        title="عرض المرفقات"
                                        class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-400 hover:bg-indigo-100 hover:text-indigo-500 transition-all">
                                        <i class="fas fa-folder-open"></i>
                                    </a>

                                    {{-- حذف --}}
                                    <button type="button"
                                        title="حذف البلاغ"
                                        wire:click="deleteReport({{ $report->id }})"
                                        wire:confirm="هل أنت متأكد من حذف هذا البلاغ؟"
                                        class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-400 hover:bg-rose-100 hover:text-rose-500 transition-all">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-400">لا توجد بلاغات حتى الآن</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $reports->links() }}
    </div>
</div>