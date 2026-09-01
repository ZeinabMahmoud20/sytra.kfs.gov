@extends('layouts.app')

@section('title', 'تعديل التكليف ' . $task->task_number)
@section('page-title', 'تعديل التكليف')

@section('content')
<div class="max-w-5xl mx-auto w-full">
    <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden form-card">

        <div class="bg-primary p-6 text-white flex items-center justify-between">
            <div>
                <h3 class="text-2xl font-black mb-1 flex items-center gap-3">
                    <i class="fas fa-pen text-accent"></i> تعديل بيانات التكليف
                </h3>
                <p class="text-slate-300 text-sm">عدّل بيانات التكليف رقم {{ $task->task_number }} المسجل بتاريخ {{ $task->received_date->format('Y-m-d') }}</p>
            </div>
            <div class="text-left">
                <span class="text-xs opacity-60 uppercase block">حالة التكليف</span>
                @include('tasks.partials.status-badge', ['status' => $task->status])
            </div>
        </div>

        @if ($errors->any())
            <div class="mx-8 mt-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('tasks.update', $task) }}" class="p-8 space-y-8">
            @csrf
            @method('PUT')

            {{-- بيانات ورود التكليف --}}
            <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 space-y-6">
                <h4 class="font-black text-primary border-r-4 border-accent pr-3">بيانات ورود التكليف</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-500">رقم التكليف</label>
                        <span class="block text-lg font-black text-primary font-mono">{{ $task->task_number }}</span>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-500">نوع المستند <span class="text-red-500">*</span></label>
                        <select name="document_type" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                            @foreach (['وارد', 'صادر'] as $type)
                                <option value="{{ $type }}" @selected($task->document_type === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-500">مصدر التكليف <span class="text-red-500">*</span></label>
                        <select name="source_id" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                            @foreach ($sources as $source)
                                <option value="{{ $source->id }}" @selected($task->source_id === $source->id)>{{ $source->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-500">الجهة المختصة للتعامل <span class="text-red-500">*</span></label>
                        <select name="entity_id" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                            @foreach ($entities as $entity)
                                <option value="{{ $entity->id }}" @selected($task->entity_id === $entity->id)>{{ $entity->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-500">تاريخ الورود <span class="text-red-500">*</span></label>
                        <input type="date" name="received_date" required
                            value="{{ $task->received_date->format('Y-m-d') }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-500">أنشأه</label>
                        <span class="block text-lg font-black text-slate-700">{{ $task->creator->name }}</span>
                    </div>
                </div>
            </div>

            {{-- التخصيص والأولوية --}}
            <div class="space-y-6">
                <h4 class="font-black text-primary border-r-4 border-accent pr-3">التخصيص والأولوية</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-black text-slate-600">المسؤول عن المتابعة والتنفيذ <span class="text-red-500">*</span></label>
                        <select name="assignee_id" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                            @foreach ($staff as $person)
                                <option value="{{ $person->id }}" @selected($task->assignee_id === $person->id)>
                                    {{ $person->name }}{{ $person->role === 'admin' ? ' (Admin)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-black text-slate-600">الأولوية <span class="text-red-500">*</span></label>
                        <select name="priority" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                            @foreach (['عالية', 'متوسطة', 'منخفضة'] as $p)
                                <option value="{{ $p }}" @selected($task->priority === $p)>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-black text-slate-600">الموعد النهائي <span class="text-red-500">*</span></label>
                        <input type="date" name="deadline" required value="{{ $task->deadline->format('Y-m-d') }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-black text-slate-600">موعد الرد</label>
                        <input type="date" name="response_date" value="{{ optional($task->response_date)->format('Y-m-d') }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-black text-slate-600">الحالة <span class="text-red-500">*</span></label>
                        <select name="status" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                            @foreach (['لم يبدأ', 'جاري التنفيذ', 'تم التنفيذ', 'متأخر', 'متوقف'] as $s)
                                <option value="{{ $s }}" @selected($task->status === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-black text-slate-600">نسبة الإنجاز %</label>
                        <input type="number" name="completion_percentage" min="0" max="100" value="{{ $task->completion_percentage }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-black text-slate-600">أيام التأخير</label>
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
                    <label class="block text-sm font-black text-slate-600">وصف التكليف ومتن المذكرة <span class="text-red-500">*</span></label>
                    <textarea name="description" required rows="4" placeholder="اكتب تفاصيل التكليف بالكامل..."
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">{{ $task->description }}</textarea>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-black text-slate-600">ملاحظات</label>
                    <textarea name="notes" rows="2"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">{{ $task->notes }}</textarea>
                </div>

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

            {{-- سبب التعديل --}}
            <div class="p-6 bg-yellow-50 rounded-2xl border border-yellow-100 space-y-4">
                <h4 class="font-black text-primary border-r-4 border-accent pr-3">سبب التعديل</h4>

                <div class="space-y-2">
                    <label class="block text-sm font-black text-slate-600">سبب التعديل (اختياري - سوف يتم تسجيله في سجل التعديلات)</label>
                    <input type="text" name="edit_note" placeholder="مثال: تعديل الموعد النهائي بناءً على طلب الجهة"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex items-center justify-between gap-4">
                <a href="{{ route('tasks.show', $task) }}"
                    class="px-6 py-4 rounded-2xl text-slate-500 font-bold hover:bg-slate-50 transition-all">
                    إلغاء
                </a>
                <button type="submit"
                    class="flex-1 bg-primary text-white font-black py-4 rounded-2xl border-2 border-transparent hover:border-accent hover:shadow-none shadow-lg text-lg transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-check-double text-accent"></i> حفظ التعديلات
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
