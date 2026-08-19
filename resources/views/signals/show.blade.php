@extends('layouts.app')

@section('title', 'عرض إشارة ' . $mainSignal->MainSignalCode)
@section('page-title', 'تفاصيل الإشارة')

@section('content')
    <div class="max-w-4xl mx-auto w-full space-y-6">

        <div class="bg-primary text-white rounded-3xl p-6 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-black">إشارة رقم {{ $mainSignal->MainSignalCode }}</h2>
                <p class="text-slate-300 text-sm">أنشأها: {{ $mainSignal->receiver->name ?? '-' }}</p>
            </div>
            <span class="px-4 py-2 bg-white/10 rounded-full font-bold">{{ $units->count() }} {{ $units->count() > 1 ? 'كروت' : 'كارت' }}</span>
        </div>

        <div class="space-y-4">
            @foreach ($units as $index => $unit)
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 {{ $index === 0 ? 'border-r-4 border-r-accent' : '' }}">
                    <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                        <div class="flex items-center gap-3">
                            @if ($index === 0)
                                <span class="px-3 py-1 bg-primary text-white rounded-full text-xs font-black">الإشارة الأساسية</span>
                            @else
                                <span class="px-3 py-1 bg-accent/10 text-accent rounded-full text-xs font-black">رد #{{ $index }}</span>
                            @endif
                            <span class="px-3 py-1 rounded-full text-xs font-black
                                {{ $unit->UNIT_SIGNAL_TYPE === 'إشارة لاسلكية' ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-600' }}">
                                {{ $unit->UNIT_SIGNAL_TYPE }}
                            </span>
                        </div>
                        <span class="text-slate-400 text-sm">{{ $unit->UNIT_SIGNAL_DATE }} - {{ $unit->UNIT_SIGNAL_TIME }}</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div><span class="text-slate-400 text-sm block">جهة الإرسال</span><span class="font-bold">{{ $unit->sender->SIGNAL_NAME ?? '-' }}</span></div>
                        <div><span class="text-slate-400 text-sm block">مضمون الإشارة</span><span class="font-bold">{{ $unit->UNIT_SIGNAL_CONTENT ?: '-' }}</span></div>
                    </div>

                    @if ($unit->UNIT_SIGNAL_SUBJECT)
                        <div class="mb-4">
                            <span class="text-slate-400 text-sm block">موضوع الإشارة</span>
                            <p class="font-bold whitespace-pre-line">{{ $unit->UNIT_SIGNAL_SUBJECT }}</p>
                        </div>
                    @endif

                    @if ($unit->authStates->isNotEmpty())
                        <div>
                            <span class="text-slate-400 text-sm block mb-2">حالة الجهات</span>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($unit->authStates as $state)
                                    <span class="px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1
                                        {{ $state->STATE === 'Correct' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        <i class="fas {{ $state->STATE === 'Correct' ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ $state->CONTACT_NAME }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="flex gap-3">
            @can('signals.edit')
                <a href="{{ route('signals.edit', $mainSignal) }}"
                    class="flex-1 text-center bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-xl">
                    <i class="fas fa-reply"></i> إضافة رد / تعديل
                </a>
            @endcan
            <a href="{{ route('signals.index') }}"
                class="flex-1 text-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3 rounded-xl">
                رجوع للقائمة
            </a>
        </div>
    </div>
@endsection