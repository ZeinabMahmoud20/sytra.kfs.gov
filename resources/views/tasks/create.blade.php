@extends('layouts.app')

@section('title', 'تسجيل تكليف جديد - متابعة التكليفات')
@section('page-title', 'تسجيل تكليف جديد')

@section('content')
<div class="max-w-5xl mx-auto w-full">
    <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden form-card">

        <div class="bg-primary p-6 text-white flex items-center justify-between">
            <div>
                <h3 class="text-2xl font-black mb-1 flex items-center gap-3">
                    <i class="fas fa-tasks text-accent"></i> استمارة تسجيل تكليف
                </h3>
                <p class="text-slate-300 text-sm">سجّل بيانات التكليف وخصّصه للموظف المسؤول عن التنفيذ .</p>
            </div>
            <div class="text-left">
                <span class="text-xs opacity-60 uppercase block">رقم التكليف التلقائي</span>
                <span class="text-xl font-mono font-bold">{{ $nextTaskNumber ?? '---' }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('tasks.store') }}" id="task-form" enctype="multipart/form-data" class="p-8 space-y-8">
            @csrf

            @if ($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            {{-- بيانات ورود التكليف --}}
            <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 space-y-6">
                <h4 class="font-black text-primary border-r-4 border-accent pr-3">بيانات ورود التكليف</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-500">تاريخ الورود <span class="text-red-500">*</span></label>
                        <input type="date" name="received_date" required
                            value="{{ old('received_date', now()->format('Y-m-d')) }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-500">نوع المستند <span class="text-red-500">*</span></label>
                        <select name="document_type" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                            <option value="" disabled {{ old('document_type') ? '' : 'selected' }}>اختر النوع</option>
                            @foreach (['وارد', 'صادر'] as $type)
                                <option value="{{ $type }}" @selected(old('document_type') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-500">مصدر التكليف <span class="text-red-500">*</span></label>
                        <select name="source_id" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                            <option value="" disabled {{ old('source_id') ? '' : 'selected' }}>اختر المصدر</option>
                            @foreach ($sources as $source)
                                <option value="{{ $source->id }}" @selected(old('source_id') == $source->id)>{{ $source->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-500">الجهة المختصة للتعامل <span class="text-red-500">*</span></label>
                        <select name="entity_id" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                            <option value="" disabled {{ old('entity_id') ? '' : 'selected' }}>اختر الجهة</option>
                            @foreach ($entities as $entity)
                                <option value="{{ $entity->id }}" @selected(old('entity_id') == $entity->id)>{{ $entity->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-bold text-slate-500">رفع المستند (اختياري)</label>
                    <div class="relative">
                        <input type="file" name="document" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                            class="w-full px-4 py-3 rounded-xl border border-dashed border-slate-300 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-accent file:text-white file:font-bold file:cursor-pointer hover:file:bg-accent-hover">
                    </div>
                    <p class="text-xs text-slate-400">الصيغ المدعومة: PDF, Word, Excel, صور (حتى 10 ميجابايت).</p>
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
                            <option value="" disabled {{ old('assignee_id') ? '' : 'selected' }}>اختر الموظف</option>
                            @foreach ($staff as $person)
                                <option value="{{ $person->id }}" @selected(old('assignee_id') == $person->id)>
                                    {{ $person->name }}{{ $person->role === 'admin' ? ' (Admin)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-400">سيصل تنبيه داخل النظام فور التسجيل.</p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-black text-slate-600">الأولوية <span class="text-red-500">*</span></label>
                        <select name="priority" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                            @foreach (['عالية', 'متوسطة', 'منخفضة'] as $p)
                                <option value="{{ $p }}" @selected(old('priority', 'متوسطة') === $p)>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-black text-slate-600">الموعد النهائي <span class="text-red-500">*</span></label>
                        <input type="date" name="deadline" required value="{{ old('deadline') }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                    </div>

                    <div class="flex items-end">
                        <p class="text-xs text-slate-400 bg-blue-50 border border-blue-100 rounded-lg px-3 py-2">
                            <i class="fas fa-circle-info text-accent"></i>
                        </p>
                    </div>
                </div>
            </div>

            {{-- وصف التكليف --}}
            <div class="p-6 bg-blue-50 rounded-2xl border border-blue-100 space-y-4">
                <h4 class="font-black text-primary border-r-4 border-accent pr-3">وصف التكليف</h4>

                <div class="space-y-2">
                    <label class="block text-sm font-black text-slate-600">وصف التكليف ومتن المذكرة <span class="text-red-500">*</span></label>
                    <textarea name="description" required rows="4" placeholder="اكتب تفاصيل التكليف بالكامل..."
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">{{ old('description') }}</textarea>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-black text-slate-600">ملاحظات (اختياري)</label>
                    <textarea name="notes" rows="2"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex items-center justify-between gap-4">
                <a href="{{ route('tasks.index') }}"
                    class="px-6 py-4 rounded-2xl text-slate-500 font-bold hover:bg-slate-50 transition-all">
                    إلغاء
                </a>
                <button type="submit"
                    class="flex-1 bg-primary text-white font-black py-4 rounded-2xl border-2 border-transparent hover:border-accent hover:shadow-none shadow-lg text-lg transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-check-double text-accent"></i> تسجيل وتخصيص التكليف
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
