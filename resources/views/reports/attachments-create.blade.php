@extends('layouts.app')

@section('title', 'إضافة مرفق - بلاغ ' . $report->REPORT_REGISTER_NUMBER)
@section('page-title', 'إضافة مرفق')

@section('content')
    <div class="max-w-lg mx-auto w-full">
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="bg-primary p-6 text-white">
                <h3 class="text-xl font-black">إضافة مرفق لبلاغ رقم {{ $report->REPORT_REGISTER_NUMBER }}</h3>
            </div>

            <form method="POST" action="{{ route('reports.attachments.store', $report) }}" enctype="multipart/form-data" class="p-8 space-y-6">
                @csrf

                @if ($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="space-y-2">
    <label class="block text-sm font-bold text-slate-500">
        نوع المرفق <span class="text-red-500">*</span>
    </label>

    <select name="AttachmentName" required
        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-purple-500">
        <option value="">-- اختر نوع المرفق --</option>
        <option value="صورة البلاغ" {{ old('AttachmentName') == 'صورة البلاغ' ? 'selected' : '' }}>
            صورة البلاغ
        </option>
        <option value="صورة متابعة البلاغ" {{ old('AttachmentName') == 'صورة متابعة البلاغ' ? 'selected' : '' }}>
            صورة متابعة البلاغ
        </option>
    </select>
</div>

<div class="space-y-2">
    <label class="block text-sm font-bold text-slate-500">
        اختر الملف (حد أقصى 10 ميجا)
    </label>

    <input type="file" name="attachment" required
        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50">
</div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-black py-3 rounded-xl">
                        <i class="fas fa-upload"></i> رفع المرفق
                    </button>
                    <a href="{{ route('reports.show', $report) }}" class="flex-1 text-center bg-slate-100 text-slate-700 font-bold py-3 rounded-xl">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
@endsection
