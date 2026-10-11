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
                <p class="text-xl">{{ $dailyAttendance->attendanceTemplate->script }}</p>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach ($dailyAttendance->dailyAttendanceEntities as $item)
                    <div class="p-6 flex items-center justify-between flex-wrap gap-3" data-entity-row>
                        <div>
                            <p class="font-bold text-primary text-lg">{{ $item->entity->name }}</p>
                            @if ($item->entity->main_location)
                                <p class="text-slate-600 text-sm mt-1">{{ $item->entity->main_location }}</p>
                            @endif
                            <p class="text-xs text-slate-400 js-response-at {{ $item->response_at ? '' : 'hidden' }}">@if ($item->response_at)آخر رد: {{ $item->response_at->format('h:i A') }}@endif</p>
                        </div>

                        <div class="flex items-center gap-2 js-status-slot" data-entity-id="{{ $item->id }}">
                            @if ($item->status === 'pending')
                                <form method="POST" action="{{ route('daily-attendance-entities.mark-done', $item) }}"
                                    class="js-mark-form">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="bg-green-600 hover:bg-green-700 text-white font-bold px-5 py-2 rounded-xl text-sm flex items-center gap-2 disabled:opacity-60">
                                        <i class="fas fa-check"></i> تم الأداء
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('daily-attendance-entities.mark-not-done', $item) }}"
                                    class="js-mark-form">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="bg-red-600 hover:bg-red-700 text-white font-bold px-5 py-2 rounded-xl text-sm flex items-center gap-2 disabled:opacity-60">
                                        <i class="fas fa-times"></i> لم يتم الأداء
                                    </button>
                                </form>
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
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div id="tmam-toast" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-[120] bg-primary text-white font-bold px-6 py-3 rounded-xl shadow-2xl flex items-center gap-2">
        <i class="fas fa-check-circle text-accent"></i>
        <span id="tmam-toast-text"></span>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const toast = document.getElementById('tmam-toast');
            let toastTimer = null;

            function showToast(message) {
                document.getElementById('tmam-toast-text').textContent = message;
                toast.classList.remove('hidden');
                clearTimeout(toastTimer);
                toastTimer = setTimeout(() => toast.classList.add('hidden'), 2500);
            }

            function badge(status) {
                if (status === 'done') {
                    return '<span class="px-4 py-2 rounded-xl text-sm font-black bg-green-100 text-green-600 flex items-center gap-2"><i class="fas fa-check-circle"></i> تم الأداء</span>';
                }
                return '<span class="px-4 py-2 rounded-xl text-sm font-black bg-red-100 text-red-600 flex items-center gap-2"><i class="fas fa-times-circle"></i> لم يتم الأداء</span>';
            }

            document.addEventListener('submit', async function(e) {
                const form = e.target.closest('.js-mark-form');
                if (!form) return;

                e.preventDefault();

                const slot = form.closest('.js-status-slot');
                const buttons = form.closest('.js-status-slot').querySelectorAll('button');
                buttons.forEach((btn) => btn.disabled = true);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new FormData(form),
                    });

                    if (!response.ok) throw new Error('request failed');

                    const data = await response.json();
                    slot.innerHTML = badge(data.status);

                    const row = slot.closest('[data-entity-row]');
                    const responseAt = row ? row.querySelector('.js-response-at') : null;
                    if (responseAt && data.response_at) {
                        responseAt.textContent = 'آخر رد: ' + data.response_at;
                        responseAt.classList.remove('hidden');
                    }

                    showToast(data.message);
                } catch (error) {
                    buttons.forEach((btn) => btn.disabled = false);
                    showToast('تعذر تنفيذ العملية، حاول مرة أخرى');
                }
            });
        })();
    </script>
@endpush