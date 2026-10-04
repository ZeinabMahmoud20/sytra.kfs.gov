@extends('layouts.app')

@section('title', 'إضافة مرفقات - بلاغ ' . $report->REPORT_REGISTER_NUMBER)
@section('page-title', 'إضافة مرفقات')

@section('content')
    <div class="max-w-2xl mx-auto w-full">
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="bg-primary p-6 text-white">
                <h3 class="text-xl font-black">إضافة مرفقات لبلاغ رقم {{ $report->REPORT_REGISTER_NUMBER }}</h3>
                <p class="text-sm opacity-80 mt-1">يمكن اختيار أكثر من ملف دفعة واحدة</p>
            </div>

            <form method="POST" action="{{ route('reports.attachments.store', $report) }}" enctype="multipart/form-data"
                id="attachment-form" class="p-8 space-y-6">
                @csrf

                @if ($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="space-y-2">
                    <label for="AttachmentName" class="block text-sm font-bold text-slate-500">
                        نوع المرفقات <span class="text-red-500">*</span>
                    </label>

                    <select name="AttachmentName" id="AttachmentName" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition-all">
                        <option value="">-- اختر نوع المرفقات --</option>
                        <option value="صورة البلاغ" @selected(old('AttachmentName') == 'صورة البلاغ')>
                            صورة البلاغ
                        </option>
                        <option value="صورة متابعة البلاغ" @selected(old('AttachmentName') == 'صورة متابعة البلاغ')>
                            صورة متابعة البلاغ
                        </option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label for="attachment" class="block text-sm font-bold text-slate-500">
                        اختر الملفات (أي صيغة - حد أقصى 100 ميجا للملف الواحد)
                    </label>

                    <div id="dropzone"
                        class="relative px-4 py-8 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 hover:bg-white transition-all text-center">
                        <i class="fas fa-cloud-arrow-up text-3xl text-slate-400 mb-3 block"></i>
                        <p class="text-slate-500 font-bold">اسحب الملفات هنا أو اضغط للاختيار</p>
                        <p class="text-xs text-slate-400 mt-1">يمكنك اختيار عدة ملفات معاً</p>

                        <input type="file" name="attachment[]" id="attachment" multiple required
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    </div>

                    <ul id="selected-files" class="space-y-2"></ul>
                </div>

                <div class="flex gap-3">
                    <button type="submit" id="submit-button"
                        class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-black py-3 rounded-xl">
                        <i class="fas fa-upload"></i> <span id="submit-label">رفع المرفقات</span>
                    </button>
                    <a href="{{ route('reports.show', $report) }}"
                        class="flex-1 text-center bg-slate-100 text-slate-700 font-bold py-3 rounded-xl">إلغاء</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                const MAX_SIZE = 100 * 1024 * 1024; // 100 ميجا لكل ملف

                const form = document.getElementById('attachment-form');
                const input = document.getElementById('attachment');
                const dropzone = document.getElementById('dropzone');
                const list = document.getElementById('selected-files');
                const submitButton = document.getElementById('submit-button');
                const submitLabel = document.getElementById('submit-label');

                function formatSize(bytes) {
                    if (bytes < 1024) return bytes + ' بايت';
                    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' كيلوبايت';
                    return (bytes / (1024 * 1024)).toFixed(1) + ' ميجا';
                }

                function renderFiles(files) {
                    list.innerHTML = '';

                    if (!files.length) {
                        dropzone.classList.remove('border-purple-400', 'bg-purple-50');
                        return;
                    }

                    dropzone.classList.add('border-purple-400', 'bg-purple-50');

                    const warning = document.createElement('li');
                    warning.className = 'text-sm font-bold text-purple-600';
                    warning.textContent = files.length > 1
                        ? 'سيتم رفع ' + files.length + ' ملفات'
                        : 'سيتم رفع ملف واحد';
                    list.appendChild(warning);

                    Array.from(files).forEach(function (file) {
                        const item = document.createElement('li');
                        item.className = 'flex items-center gap-3 p-3 rounded-xl bg-white border border-slate-100';

                        const icon = document.createElement('i');
                        icon.className = 'fas fa-file text-slate-400 text-xl';

                        const name = document.createElement('span');
                        name.className = 'font-bold text-slate-700 truncate';
                        name.textContent = file.name;

                        const size = document.createElement('span');
                        size.className = 'text-slate-400 text-sm mr-auto whitespace-nowrap';
                        size.textContent = formatSize(file.size);

                        item.appendChild(icon);
                        item.appendChild(name);
                        item.appendChild(size);

                        if (file.size > MAX_SIZE) {
                            item.classList.add('border-red-200', 'bg-red-50');
                            size.className = 'text-red-500 text-sm mr-auto whitespace-nowrap';
                            size.textContent = 'يتجاوز 100 ميجا';
                        }

                        list.appendChild(item);
                    });
                }

                input.addEventListener('change', function () {
                    renderFiles(input.files);
                });

                ['dragenter', 'dragover'].forEach(function (event) {
                    dropzone.addEventListener(event, function (e) {
                        e.preventDefault();
                        dropzone.classList.add('border-purple-400', 'bg-purple-50');
                    });
                });

                ['dragleave', 'drop'].forEach(function (event) {
                    dropzone.addEventListener(event, function (e) {
                        e.preventDefault();
                        dropzone.classList.remove('border-purple-400', 'bg-purple-50');
                    });
                });

                dropzone.addEventListener('drop', function (e) {
                    if (!e.dataTransfer || !e.dataTransfer.files.length) return;

                    // نحافظ على الملفات المختارة يدوياً + الملفات المسحوبة
                    const transferred = new DataTransfer();
                    Array.from(input.files || []).forEach(function (file) {
                        transferred.items.add(file);
                    });
                    Array.from(e.dataTransfer.files).forEach(function (file) {
                        transferred.items.add(file);
                    });

                    input.files = transferred.files;
                    renderFiles(input.files);
                });

                form.addEventListener('submit', function () {
                    // منع الإرسال المزدوج أثناء الرفع
                    submitButton.disabled = true;
                    submitButton.classList.add('opacity-60', 'cursor-not-allowed');
                    submitLabel.textContent = 'جارٍ الرفع...';
                });
            })();
        </script>
    @endpush
@endsection