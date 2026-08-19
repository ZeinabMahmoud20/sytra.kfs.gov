@extends('layouts.app')

@section('title', $dailyAttendance->attendanceTemplate->name . ' - نظام التمامات')
@section('page-title', $dailyAttendance->attendanceTemplate->name)

@section('content')
    <div class="max-w-4xl mx-auto w-full">
        <a href="{{ route('daily-attendances.index') }}"
            class="inline-flex items-center gap-2 text-primary font-bold mb-4 hover:text-accent transition-colors">
            <i class="fas fa-arrow-right"></i> رجوع لتمامات اليوم
        </a>

        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm font-bold">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden mb-6">
            <div class="bg-primary p-6 text-white space-y-2">
                <h3 class="text-2xl font-black flex items-center gap-3">
                    <i class="fas fa-clipboard-check text-accent"></i> {{ $dailyAttendance->attendanceTemplate->name }}
                </h3>
                <p class="text-white/70 text-sm">{{ $dailyAttendance->attendance_date->format('Y-m-d') }} — {{ $dailyAttendance->attendanceTemplate->attendance_time->format('h:i A') }}</p>
            </div>

            <div class="p-6 bg-slate-50 border-b border-slate-100">
                <p class="text-xs font-black text-slate-500 mb-1">نص التمام</p>
                <p class="text-slate-700">{{ $dailyAttendance->attendanceTemplate->script }}</p>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach ($dailyAttendance->dailyAttendanceEntities as $item)
                    <div class="p-6 flex items-center justify-between flex-wrap gap-3">
                        <div>
                            <p class="font-bold text-primary text-lg">{{ $item->entity->name }}</p>
                            @if ($item->response_at)
                                <p class="text-xs text-slate-400">آخر رد: {{ $item->response_at->format('h:i A') }}</p>
                            @endif
                        </div>

                        @if ($item->status === 'pending')
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('daily-attendance-entities.mark-done', $item) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="bg-green-600 hover:bg-green-700 text-white font-bold px-5 py-2 rounded-xl text-sm flex items-center gap-2">
                                        <i class="fas fa-check"></i> تم الأداء
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('daily-attendance-entities.mark-not-done', $item) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="bg-red-600 hover:bg-red-700 text-white font-bold px-5 py-2 rounded-xl text-sm flex items-center gap-2">
                                        <i class="fas fa-times"></i> لم يتم الأداء
                                    </button>
                                </form>
                            </div>
                        @elseif ($item->status === 'done')
                            <span class="px-4 py-2 rounded-xl text-sm font-black bg-green-100 text-green-600 flex items-center gap-2">
                                <i class="fas fa-check-circle"></i> تم الأداء
                            </span>
                        @else
                            <span class="px-4 py-2 rounded-xl text-sm font-black bg-red-100 text-red-600 flex items-center gap-2">
                                <i class="fas fa-times-circle"></i> لم يتم الأداء
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection