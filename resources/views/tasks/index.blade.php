@extends('layouts.app')

@section('title', 'متابعة التكليفات')

@section('content')
<div class="max-w-7xl mx-auto py-8" dir="rtl">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">متابعة التكليفات</h1>
            <p class="text-gray-500 text-sm mt-1">إجمالي {{ $tasks->total() }} تكليف</p>
        </div>

        @can('create', \App\Models\TaskAssignment::class)
            <a href="{{ route('tasks.create') }}"
               class="px-5 py-2.5 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 flex items-center gap-2">
                <span>+</span> تكليف جديد
            </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-300 text-green-700 rounded-lg p-3 mb-5 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- بحث بسيط + فلاتر متقدمة --}}
    <form method="GET" class="bg-white rounded-2xl border border-gray-200 p-4 mb-5 space-y-3">
        <div class="flex flex-col md:flex-row gap-3">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="بحث برقم التكليف / المسؤول / الوصف..."
                   class="flex-1 rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
            <button type="submit" class="px-5 py-2 rounded-lg bg-gray-800 text-white text-sm">بحث</button>
            <button type="button" onclick="document.getElementById('advanced-filters').classList.toggle('hidden')"
                    class="px-5 py-2 rounded-lg border border-gray-300 text-sm text-gray-600">فلاتر متقدمة</button>
        </div>

        <div id="advanced-filters" class="hidden grid grid-cols-2 md:grid-cols-6 gap-3 pt-3 border-t border-gray-100">
            <select name="status" class="rounded-lg border-gray-300 text-sm">
                <option value="">كل الحالات</option>
                @foreach (['لم يبدأ', 'جاري التنفيذ', 'تم التنفيذ', 'متأخر', 'متوقف'] as $s)
                    <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ $s }}</option>
                @endforeach
            </select>

            <select name="priority" class="rounded-lg border-gray-300 text-sm">
                <option value="">كل الأولويات</option>
                @foreach (['عالية', 'متوسطة', 'منخفضة'] as $p)
                    <option value="{{ $p }}" @selected(($filters['priority'] ?? '') === $p)>{{ $p }}</option>
                @endforeach
            </select>

            <select name="entity_id" class="rounded-lg border-gray-300 text-sm">
                <option value="">كل الجهات</option>
                @foreach ($entities as $entity)
                    <option value="{{ $entity->id }}" @selected(($filters['entity_id'] ?? '') == $entity->id)>{{ $entity->name }}</option>
                @endforeach
            </select>

            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="rounded-lg border-gray-300 text-sm" placeholder="من تاريخ">
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="rounded-lg border-gray-300 text-sm" placeholder="إلى تاريخ">

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="overdue_only" value="1" @checked($filters['overdue_only'] ?? false)>
                المتأخر فقط
            </label>
        </div>
    </form>

    <div class="bg-white rounded-2xl border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm text-right">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3">رقم التكليف</th>
                    <th class="px-4 py-3">الجهة</th>
                    <th class="px-4 py-3">المسؤول</th>
                    <th class="px-4 py-3">الأولوية</th>
                    <th class="px-4 py-3">الموعد النهائي</th>
                    <th class="px-4 py-3">نسبة الإنجاز</th>
                    <th class="px-4 py-3">الحالة</th>
                    <th class="px-4 py-3">أيام التأخير</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($tasks as $task)
                    <tr class="hover:bg-gray-50 {{ $task->is_overdue ? 'bg-orange-50/40' : '' }}">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $task->task_number }}</td>
                        <td class="px-4 py-3">{{ $task->entity->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $task->assignee->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $task->priority }}</td>
                        <td class="px-4 py-3">{{ $task->deadline->format('Y-m-d') }}</td>
                        <td class="px-4 py-3">
                            <div class="w-24 bg-gray-100 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $task->completion_percentage }}%"></div>
                            </div>
                            <span class="text-xs text-gray-500">{{ $task->completion_percentage }}%</span>
                        </td>
                        <td class="px-4 py-3">@include('tasks.partials.status-badge', ['status' => $task->status])</td>
                        <td class="px-4 py-3">
                            @if ($task->delay_days > 0)
                                <span class="text-orange-600 font-semibold">{{ $task->delay_days }} يوم</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('tasks.show', $task) }}" class="text-blue-600 hover:underline">تفاصيل</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-gray-400">لا توجد تكليفات مطابقة</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $tasks->links() }}</div>
</div>
@endsection
