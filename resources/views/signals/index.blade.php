@extends('layouts.app')

@section('title', 'قائمة الإشارات - الشبكة الوطنية للطوارئ')
@section('page-title', 'قائمة الإشارات')

@section('content')
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <h1 class="text-2xl font-black text-primary">كل الإشارات</h1>
        <div class="flex items-center gap-3 flex-wrap">
            @can('signals.export_excel')
                <a href="{{ route('signals.export.excel', request()->query()) }}"
                    class="bg-green-600 hover:bg-green-700 text-white font-bold px-4 py-3 rounded-xl transition-all flex items-center gap-2 text-sm">
                    <i class="fas fa-file-excel"></i> تصدير Excel
                </a>
            @endcan
            @can('signals.export_pdf')
                <a href="{{ route('signals.export.pdf', request()->query()) }}"
                    class="bg-red-600 hover:bg-red-700 text-white font-bold px-4 py-3 rounded-xl transition-all flex items-center gap-2 text-sm">
                    <i class="fas fa-file-pdf"></i> تصدير PDF
                </a>
            @endcan
            @can('signals.create')
                <a href="{{ route('signals.create') }}"
                    class="bg-accent hover:bg-accent-hover text-white font-bold px-5 py-3 rounded-xl transition-all flex items-center gap-2">
                    <i class="fas fa-plus"></i> إضافة إشارة جديدة
                </a>
            @endcan
        </div>
    </div>

    {{-- فلاتر التاريخ/الوقت --}}
    <form method="GET" action="{{ route('signals.index') }}"
        class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mb-6 flex flex-wrap items-center gap-4">
        <div class="space-y-1">
            <label class="text-xs font-bold text-slate-500 block">من تاريخ</label>
            <input type="date" name="date_from" value="{{ request('date_from', now()->format('Y-m-d')) }}"
                class="px-3 py-2 rounded-lg border border-slate-200 text-sm">
        </div>
        <div class="space-y-1">
            <label class="text-xs font-bold text-slate-500 block">إلى تاريخ</label>
            <input type="date" name="date_to" value="{{ request('date_to', now()->format('Y-m-d')) }}"
                class="px-3 py-2 rounded-lg border border-slate-200 text-sm">
        </div>

        <label class="flex items-center gap-2 text-sm font-bold text-slate-600 cursor-pointer">
            <input type="checkbox" name="filter_by_time" value="1" @checked(request()->boolean('filter_by_time')) class="w-4 h-4 accent-accent">
            فلترة بالوقت
        </label>
        <div class="space-y-1">
            <label class="text-xs font-bold text-slate-500 block">من وقت</label>
            <input type="time" name="time_from" value="{{ request('time_from') }}" class="px-3 py-2 rounded-lg border border-slate-200 text-sm">
        </div>
        <div class="space-y-1">
            <label class="text-xs font-bold text-slate-500 block">إلى وقت</label>
            <input type="time" name="time_to" value="{{ request('time_to') }}" class="px-3 py-2 rounded-lg border border-slate-200 text-sm">
        </div>

        <button type="submit" class="bg-primary hover:bg-primary/90 text-white font-bold px-6 py-2 rounded-lg text-sm flex items-center gap-2 mr-auto">
            <i class="fas fa-filter"></i> بحث
        </button>
    </form>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead class="bg-slate-50 text-slate-500 text-sm">
                    <tr>
                        <th class="px-4 py-4 font-bold">كود الإشارة</th>
                        <th class="px-4 py-4 font-bold">مضمون الإشارة</th>
                        <th class="px-4 py-4 font-bold">نوع الإشارة</th>
                        <th class="px-4 py-4 font-bold">موضوع الإشارة</th>
                        <th class="px-4 py-4 font-bold">تاريخ الإشارة</th>
                        <th class="px-4 py-4 font-bold">توقيت الإشارة</th>
                        <th class="px-4 py-4 font-bold">متلقي الإشارة</th>
                        <th class="px-4 py-4 font-bold text-center">عدد الردود</th>
                        <th class="px-4 py-4 font-bold text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($units as $unit)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-4 font-bold text-primary">{{ $unit->MainSignalCode }}</td>
                            <td class="px-4 py-4 text-slate-500">{{ \Illuminate\Support\Str::limit($unit->UNIT_SIGNAL_CONTENT, 30) }}</td>
                            <td class="px-4 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-black
                                    {{ $unit->UNIT_SIGNAL_TYPE === 'إشارة لاسلكية' ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-600' }}">
                                    {{ $unit->UNIT_SIGNAL_TYPE }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-slate-500">{{ \Illuminate\Support\Str::limit($unit->UNIT_SIGNAL_SUBJECT, 30) }}</td>
                            <td class="px-4 py-4 text-slate-500">{{ $unit->UNIT_SIGNAL_DATE }}</td>
                            <td class="px-4 py-4 text-slate-500">{{ $unit->UNIT_SIGNAL_TIME }}</td>
                            <td class="px-4 py-4 text-slate-500">{{ $unit->receiver_name }}</td>
                            <td class="px-4 py-4 text-center">
                                @if ($unit->replies_count > 1)
                                    <span class="px-2 py-1 bg-accent/10 text-accent rounded-full text-xs font-black">
                                        {{ $unit->replies_count - 1 }} رد
                                    </span>
                                @else
                                    <span class="text-slate-300 text-xs">بدون ردود</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @can('signals.view')
                                        <a href="{{ route('signals.show', $unit->MAIN_SEND_ID) }}" title="عرض"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100">
                                            <i class="fas fa-eye text-sm"></i>
                                        </a>
                                    @endcan
                                    @can('signals.edit')
                                        <a href="{{ route('signals.edit', $unit->MAIN_SEND_ID) }}" title="تعديل/رد"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100">
                                            <i class="fas fa-edit text-sm"></i>
                                        </a>
                                    @endcan
                                    @can('signals.delete')
                                        <form method="POST" action="{{ route('signals.destroy', $unit->MAIN_SEND_ID) }}"
                                            onsubmit="return confirm('متأكد إنك عايز تحذف هذه الإشارة؟ هيتم حذف كل الردود المرتبطة بيها كمان.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="حذف"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100">
                                                <i class="fas fa-trash text-sm"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-slate-400">لا توجد إشارات مطابقة</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $units->links() }}</div>
@endsection
