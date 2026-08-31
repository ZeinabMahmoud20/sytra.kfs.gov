@extends('layouts.app')

@section('title', 'تعديل التكليف ' . $task->task_number)

@section('content')
<div class="max-w-4xl mx-auto py-8" dir="rtl">

    <h1 class="text-2xl font-bold text-gray-800 mb-6">تعديل التكليف {{ $task->task_number }}</h1>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-700 rounded-lg p-4 mb-6">
            <ul class="list-disc mr-5 space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('tasks.update', $task) }}" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">نوع المستند *</label>
                <select name="document_type" class="w-full rounded-lg border-gray-300" required>
                    @foreach (['وارد', 'صادر'] as $type)
                        <option value="{{ $type }}" @selected($task->document_type === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">مصدر التكليف *</label>
                <select name="source_id" class="w-full rounded-lg border-gray-300" required>
                    @foreach ($sources as $source)
                        <option value="{{ $source->id }}" @selected($task->source_id === $source->id)>{{ $source->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الجهة المختصة *</label>
                <select name="entity_id" class="w-full rounded-lg border-gray-300" required>
                    @foreach ($entities as $entity)
                        <option value="{{ $entity->id }}" @selected($task->entity_id === $entity->id)>{{ $entity->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">المسؤول عن التنفيذ *</label>
                <select name="assignee_id" class="w-full rounded-lg border-gray-300" required>
                    @foreach ($staff as $person)
                        <option value="{{ $person->id }}" @selected($task->assignee_id === $person->id)>{{ $person->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">لو غيّرت الموظف، هيوصله تنبيه جديد فوراً.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الأولوية *</label>
                <select name="priority" class="w-full rounded-lg border-gray-300" required>
                    @foreach (['عالية', 'متوسطة', 'منخفضة'] as $p)
                        <option value="{{ $p }}" @selected($task->priority === $p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الموعد النهائي *</label>
                <input type="date" name="deadline" value="{{ $task->deadline->format('Y-m-d') }}" class="w-full rounded-lg border-gray-300" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">موعد الرد</label>
                <input type="date" name="response_date" value="{{ optional($task->response_date)->format('Y-m-d') }}" class="w-full rounded-lg border-gray-300">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الحالة *</label>
                <select name="status" class="w-full rounded-lg border-gray-300" required>
                    @foreach (['لم يبدأ', 'جاري التنفيذ', 'تم التنفيذ', 'متأخر', 'متوقف'] as $s)
                        <option value="{{ $s }}" @selected($task->status === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">نسبة الإنجاز %</label>
                <input type="number" name="completion_percentage" min="0" max="100" value="{{ $task->completion_percentage }}" class="w-full rounded-lg border-gray-300">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">وصف التكليف *</label>
            <textarea name="description" rows="4" class="w-full rounded-lg border-gray-300" required>{{ $task->description }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
            <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300">{{ $task->notes }}</textarea>
        </div>

        <div class="bg-blue-50 rounded-lg p-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">سبب التعديل (اختياري - هيتسجل في سجل التعديلات)</label>
            <input type="text" name="edit_note" class="w-full rounded-lg border-gray-300" placeholder="مثال: تعديل الموعد النهائي بناءً على طلب الجهة">
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('tasks.show', $task) }}" class="px-5 py-2.5 rounded-lg text-gray-600 hover:bg-gray-100">إلغاء</a>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700">حفظ التعديلات</button>
        </div>
    </form>
</div>
@endsection
