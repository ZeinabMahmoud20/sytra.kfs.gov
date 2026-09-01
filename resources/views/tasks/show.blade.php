@extends('layouts.app')

@section('title', 'تفاصيل التكليف ' . $task->task_number)
@section('page-title', 'تفاصيل التكليف')

@section('content')
<div class="max-w-5xl mx-auto w-full">
    <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden form-card">

        <div class="bg-primary p-6 text-white flex items-center justify-between">
            <div>
                <h3 class="text-2xl font-black mb-1 flex items-center gap-3">
                    <i class="fas fa-file-alt text-accent"></i> بيانات التكليف
                </h3>
                <p class="text-slate-300 text-sm">تفاصيل التكليف رقم {{ $task->task_number }} المسجل بتاريخ {{ $task->received_date->format('Y-m-d') }}</p>
            </div>
            <div class="text-left">
                <span class="text-xs opacity-60 uppercase block">حالة التكليف</span>
                @include('tasks.partials.status-badge', ['status' => $task->status])
            </div>
        </div>

        @if (session('success'))
            <div class="mx-8 mt-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm flex items-center gap-3">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <div class="p-8 space-y-8">

            {{-- بيانات ورود التكليف --}}
            <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 space-y-6">
                <h4 class="font-black text-primary border-r-4 border-accent pr-3">بيانات ورود التكليف</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="space-y-1">
                        <span class="block text-sm font-bold text-slate-500">رقم التكليف</span>
                        <span class="block text-lg font-black text-primary font-mono">{{ $task->task_number }}</span>
                    </div>

                    <div class="space-y-1">
                        <span class="block text-sm font-bold text-slate-500">نوع المستند</span>
                        <span class="block text-lg font-black text-slate-700">{{ $task->document_type }}</span>
                    </div>

                    <div class="space-y-1">
                        <span class="block text-sm font-bold text-slate-500">مصدر التكليف</span>
                        <span class="block text-lg font-black text-slate-700">{{ $task->source->name }}</span>
                    </div>

                    <div class="space-y-1">
                        <span class="block text-sm font-bold text-slate-500">الجهة المختصة للتعامل</span>
                        <span class="block text-lg font-black text-slate-700">{{ $task->entity->name }}</span>
                    </div>

                    <div class="space-y-1">
                        <span class="block text-sm font-bold text-slate-500">تاريخ الورود</span>
                        <span class="block text-lg font-black text-slate-700">{{ $task->received_date->format('Y-m-d') }}</span>
                    </div>

                    <div class="space-y-1">
                        <span class="block text-sm font-bold text-slate-500">أنشأه</span>
                        <span class="block text-lg font-black text-slate-700">{{ $task->creator->name }}</span>
                    </div>
                </div>
            </div>

            {{-- التخصيص والأولوية --}}
            <div class="space-y-6">
                <h4 class="font-black text-primary border-r-4 border-accent pr-3">التخصيص والأولوية</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <span class="block text-sm font-black text-slate-600">المسؤول عن المتابعة والتنفيذ</span>
                        <span class="block text-lg font-black text-slate-700">{{ $task->assignee->name }}</span>
                    </div>

                    <div class="space-y-1">
                        <span class="block text-sm font-black text-slate-600">الأولوية</span>
                        <span class="inline-block px-4 py-1.5 rounded-xl font-black
                            {{ $task->priority === 'عالية' ? 'bg-red-100 text-red-700 border border-red-300' : ($task->priority === 'متوسطة' ? 'bg-yellow-100 text-yellow-700 border border-yellow-300' : 'bg-green-100 text-green-700 border border-green-300') }}">
                            {{ $task->priority }}
                        </span>
                    </div>

                    <div class="space-y-1">
                        <span class="block text-sm font-black text-slate-600">الموعد النهائي</span>
                        <span class="block text-lg font-black text-slate-700 {{ $task->is_overdue ? 'text-red-600' : '' }}">{{ $task->deadline->format('Y-m-d') }}</span>
                    </div>

                    <div class="space-y-1">
                        <span class="block text-sm font-black text-slate-600">موعد الرد</span>
                        <span class="block text-lg font-black text-slate-700">{{ optional($task->response_date)->format('Y-m-d') ?? '-' }}</span>
                    </div>

                    <div class="space-y-1">
                        <span class="block text-sm font-black text-slate-600">نسبة الإنجاز</span>
                        <span class="block text-lg font-black text-slate-700">{{ $task->completion_percentage ?? 0 }}%</span>
                    </div>

                    <div class="space-y-1">
                        <span class="block text-sm font-black text-slate-600">أيام التأخير</span>
                        <span class="block text-lg font-black {{ $task->delay_days > 0 ? 'text-orange-600' : 'text-green-600' }}">
                            {{ $task->delay_days > 0 ? $task->delay_days . ' يوم' : 'لا يوجد' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- وصف التكليف --}}
            <div class="p-6 bg-blue-50 rounded-2xl border border-blue-100 space-y-4">
                <h4 class="font-black text-primary border-r-4 border-accent pr-3">وصف التكليف</h4>

                <div class="space-y-2">
                    <span class="block text-sm font-black text-slate-600">وصف التكليف ومتن المذكرة</span>
                    <p class="text-slate-700 leading-relaxed whitespace-pre-line">{{ $task->description }}</p>
                </div>

                @if ($task->notes)
                    <div class="space-y-2 pt-4 border-t border-blue-100">
                        <span class="block text-sm font-black text-slate-600">ملاحظات</span>
                        <p class="text-slate-700 leading-relaxed whitespace-pre-line">{{ $task->notes }}</p>
                    </div>
                @endif

                @if ($task->document_path)
                    <div class="space-y-2 pt-4 border-t border-blue-100">
                        <span class="block text-sm font-black text-slate-600 mb-2">المستند المرفق</span>
                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($task->document_path) }}"
                           target="_blank"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-accent text-white font-bold hover:bg-accent-hover transition-all shadow-sm">
                            <i class="fas fa-file-download"></i> تحميل / عرض المستند
                        </a>
                    </div>
                @endif
            </div>

            @can('update', $task)
                <div class="pt-2">
                    <a href="{{ route('tasks.edit', $task) }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-bold hover:bg-slate-200 transition-all">
                        <i class="fas fa-pen text-primary"></i> تعديل بيانات التكليف
                    </a>
                </div>
            @endcan

            {{-- تحديث الحالة سريعاً (للموظف صاحب التكليف) --}}
            @can('updateStatus', $task)
                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 space-y-6">
                    <h4 class="font-black text-primary border-r-4 border-accent pr-3"> حالة التنفيذ</h4>
                    <form method="POST" action="{{ route('tasks.update', $task) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="block text-sm font-bold text-slate-500">الحالة</label>
                                <select name="status" disabled
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-100 text-slate-500 cursor-not-allowed focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                                    @foreach (['لم يبدأ', 'جاري التنفيذ', 'تم التنفيذ', 'متأخر', 'متوقف'] as $s)
                                        <option value="{{ $s }}" @selected($task->status === $s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-bold text-slate-500">نسبة الإنجاز %</label>
                                <input type="number" name="completion_percentage" min="0" max="100" readonly
                                       value="{{ $task->completion_percentage }}"
                                       class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-100 text-slate-500 cursor-not-allowed focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-bold text-slate-500">موعد الرد</label>
                                <input type="date" name="response_date" value="{{ optional($task->response_date)->format('Y-m-d') }}" disabled
                                       class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-100 text-slate-500 cursor-not-allowed focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-slate-500">ملاحظة (سبب التحديث)</label>
                            <textarea name="notes" rows="2"
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">{{ $task->notes }}</textarea>
                        </div>

                      
                    </form>
                </div>
            @endcan

            {{-- رجوع لقائمة التكليفات --}}
            <div class="pt-6 border-t border-slate-100 flex items-center justify-between gap-4">
                <a href="{{ route('tasks.index') }}"
                    class="px-6 py-4 rounded-2xl bg-slate-100 text-slate-700 font-bold hover:bg-slate-200 transition-all inline-flex items-center gap-2">
                    <i class="fas fa-arrow-right"></i> رجوع لقائمة التكليفات
                </a>
            </div>

        </div>
    </div>
</div>
@endsection
