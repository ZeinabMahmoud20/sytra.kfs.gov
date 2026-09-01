@extends('layouts.app')

@section('title', 'رفع دليل الاتصال من Excel')
@section('page-title', 'رفع دليل الاتصال من Excel')

@section('content')
    <div class="max-w-2xl mx-auto w-full">
        <a href="{{ route('contact-guides.index') }}"
            class="inline-flex items-center gap-2 text-primary font-bold mb-4 hover:text-accent transition-colors">
            <i class="fas fa-arrow-right"></i> رجوع لدليل الاتصال
        </a>

        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="bg-primary p-6 text-white">
                <h3 class="text-2xl font-black flex items-center gap-3">
                    <i class="fas fa-file-excel text-accent"></i> رفع دليل الاتصال من ملف Excel
                </h3>
            </div>

            <form method="POST" action="{{ route('contact-guides.import') }}" enctype="multipart/form-data" class="p-8 space-y-6">
                @csrf

                @if ($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl text-blue-700 text-sm space-y-1">
                    <p><i class="fas fa-info-circle"></i> الصف الأول يجب أن يحتوي على الأعمدة التالية (بعنوان بالإنجليزية):</p>
                    <ul class="pr-6 space-y-0.5" dir="ltr" style="text-align: left;">
                        <li><strong>department_name</strong> (اسم الإدارة) <span class="text-red-500">*</span></li>
                        <li><strong>manager_name</strong> (مدير الإدارة)</li>
                        <li><strong>phone_number</strong> (رقم الهاتف)</li>
                        <li><strong>landline_number</strong> (رقم الأرضي)</li>
                        <li><strong>additional_phone</strong> (هاتف إضافي)</li>
                    </ul>
                    <p class="mt-2">
                        عند إعادة رفع الملف: تُضاف الإدارات الجديدة تلقائيًا، ويتم تحديث بيانات الإدارات الموجودة
                        (بنفس اسم الإدارة). التكرار داخل نفس الملف يتم تجاهله.
                    </p>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-black text-slate-600">ملف دليل الاتصال <span class="text-red-500">*</span></label>
                    <input type="file" name="file" required accept=".xlsx,.xls,.csv"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                </div>

                <div class="pt-6 border-t border-slate-100">
                    <button type="submit"
                        class="w-full bg-primary text-white font-black py-4 rounded-2xl border-2 border-transparent hover:border-accent shadow-lg text-lg transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-upload text-accent"></i> رفع الملف
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection