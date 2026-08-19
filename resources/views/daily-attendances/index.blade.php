@extends('layouts.app')

@section('title', 'تمامات اليوم - نظام التمامات')
@section('page-title', 'تمامات اليوم')

@section('content')
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <h1 class="text-2xl font-black text-primary">تمامات اليوم — {{ now()->format('Y-m-d') }}</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($dailyAttendances as $daily)
            <a href="{{ route('daily-attendances.show', $daily) }}"
                class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 hover:shadow-md transition-all block">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-black text-primary text-lg">{{ $daily->attendanceTemplate->name }}</h3>

                    @php
                        $statusMap = [
                            'created' => ['label' => 'لم يبدأ', 'class' => 'bg-slate-100 text-slate-500'],
                            'in_progress' => ['label' => 'جاري', 'class' => 'bg-amber-100 text-amber-600'],
                            'completed' => ['label' => 'مكتمل', 'class' => 'bg-green-100 text-green-600'],
                        ];
                        $status = $statusMap[$daily->status] ?? $statusMap['created'];
                    @endphp

                    <span class="px-3 py-1 rounded-full text-xs font-black {{ $status['class'] }}">
                        {{ $status['label'] }}
                    </span>
                </div>

                <div class="flex items-center gap-2 text-slate-500 text-sm mb-3">
                    <i class="fas fa-clock text-accent"></i>
                    {{ $daily->attendanceTemplate->attendance_time->format('h:i A') }}
                </div>

                @php
                    $total = $daily->dailyAttendanceEntities->count();
                    $done = $daily->dailyAttendanceEntities->where('status', '!=', 'pending')->count();
                @endphp

                <div class="w-full bg-slate-100 rounded-full h-2 mb-2">
                    <div class="bg-accent h-2 rounded-full" style="width: {{$total > 0 ? ($done / $total * 100) : 0}}%"></div>
                </div>
                <p class="text-xs text-slate-400">{{ $done }} من {{ $total }} جهة تم الرد عليها</p>
            </a>
        @empty
            <div class="col-span-full bg-white rounded-3xl shadow-sm border border-slate-100 p-8 text-center text-slate-400">
                لا توجد تمامات لهذا اليوم
            </div>
        @endforelse
    </div>
@endsection