@extends('layouts.app')

@section('title', 'تفاصيل التكليف ' . $task->task_number)

@section('content')
<div class="max-w-4xl mx-auto py-8" dir="rtl">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $task->task_number }}</h1>
            <p class="text-gray-500 text-sm mt-1">تسجيل بتاريخ {{ $task->received_date->format('Y-m-d') }}</p>
        </div>
        @include('tasks.partials.status-badge', ['status' => $task->status])
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-300 text-green-700 rounded-lg p-3 mb-5 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- بيانات التكليف الأساسية --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500">مصدر التكليف:</span> <strong>{{ $task->source->name }}</strong></div>
            <div><span class="text-gray-500">الجهة المختصة:</span> <strong>{{ $task->entity->name }}</strong></div>
            <div><span class="text-gray-500">نوع المستند:</span> <strong>{{ $task->document_type }}</strong></div>
            <div><span class="text-gray-500">المسؤول عن التنفيذ:</span> <strong>{{ $task->assignee->name }}</strong></div>
            <div><span class="text-gray-500">الأولوية:</span> <strong>{{ $task->priority }}</strong></div>
            <div><span class="text-gray-500">الموعد النهائي:</span> <strong>{{ $task->deadline->format('Y-m-d') }}</strong></div>
            <div><span class="text-gray-500">موعد الرد:</span> <strong>{{ optional($task->response_date)->format('Y-m-d') ?? '-' }}</strong></div>
            <div>
                <span class="text-gray-500">أيام التأخير:</span>
                <strong class="{{ $task->delay_days > 0 ? 'text-orange-600' : '' }}">
                    {{ $task->delay_days > 0 ? $task->delay_days . ' يوم' : 'لا يوجد' }}
                </strong>
            </div>
            <div><span class="text-gray-500">أنشأه:</span> <strong>{{ $task->creator->name }}</strong></div>
        </div>

        <div class="mt-4 pt-4 border-t border-gray-100">
            <span class="text-gray-500 text-sm block mb-1">وصف التكليف:</span>
            <p class="text-gray-800">{{ $task->description }}</p>
        </div>

        @if ($task->notes)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <span class="text-gray-500 text-sm block mb-1">ملاحظات:</span>
                <p class="text-gray-800">{{ $task->notes }}</p>
            </div>
        @endif

        @if ($task->document_path)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <span class="text-gray-500 text-sm block mb-2">المستند المرفق:</span>
                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($task->document_path) }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-50 text-blue-700 text-sm font-semibold hover:bg-blue-100">
                    <i class="fas fa-file-download"></i> تحميل / عرض المستند
                </a>
            </div>
        @endif

        @can('update', $task)
            <div class="mt-5 pt-4 border-t border-gray-100">
                <a href="{{ route('tasks.edit', $task) }}" class="text-blue-600 text-sm font-semibold hover:underline">
                    ✎ تعديل بيانات التكليف
                </a>
            </div>
        @endcan
    </div>

    {{-- تحديث الحالة سريعاً (للموظف صاحب التكليف) --}}
    @can('updateStatus', $task)
        <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
            <h2 class="font-semibold text-gray-800 mb-4">تحديث حالة التنفيذ</h2>
            <form method="POST" action="{{ route('tasks.update', $task) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">الحالة</label>
                        <select name="status" class="w-full rounded-lg border-gray-300 text-sm">
                            @foreach (['لم يبدأ', 'جاري التنفيذ', 'تم التنفيذ', 'متأخر', 'متوقف'] as $s)
                                <option value="{{ $s }}" @selected($task->status === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">نسبة الإنجاز %</label>
                        <input type="number" name="completion_percentage" min="0" max="100"
                               value="{{ $task->completion_percentage }}" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">موعد الرد</label>
                        <input type="date" name="response_date" value="{{ optional($task->response_date)->format('Y-m-d') }}"
                               class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">ملاحظة (سبب التحديث)</label>
                    <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 text-sm">{{ $task->notes }}</textarea>
                </div>

                <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
                    حفظ التحديث
                </button>
            </form>
        </div>
    @endcan

    {{-- سجل التعديلات (Audit Trail) --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <h2 class="font-semibold text-gray-800 mb-4">سجل التعديلات</h2>

        @forelse ($task->logs as $log)
            <div class="flex items-start gap-3 py-3 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                <div class="w-2 h-2 rounded-full bg-blue-400 mt-2"></div>
                <div class="flex-1 text-sm">
                    <p class="text-gray-800">
                        <strong>{{ $log->changedBy->name }}</strong>
                        غيّر <strong>{{ $log->field_label }}</strong>
                        @if ($log->old_value || $log->new_value)
                            من "<span class="text-gray-500">{{ $log->old_value ?: '-' }}</span>"
                            إلى "<span class="text-gray-700 font-medium">{{ $log->new_value ?: '-' }}</span>"
                        @endif
                    </p>
                    @if ($log->note)
                        <p class="text-gray-500 text-xs mt-1">ملاحظة: {{ $log->note }}</p>
                    @endif
                    <p class="text-gray-400 text-xs mt-1">{{ $log->created_at->format('Y-m-d H:i') }}</p>
                </div>
            </div>
        @empty
            <p class="text-gray-400 text-sm">لا يوجد تعديلات مسجلة بعد.</p>
        @endforelse
    </div>
</div>
@endsection
