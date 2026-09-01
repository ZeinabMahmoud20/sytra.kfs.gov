@extends('layouts.app')

@section('title', 'إضافة جهة اتصال - دليل الاتصال')
@section('page-title', 'إضافة جهة اتصال')

@section('content')
    <div class="max-w-2xl mx-auto w-full">
        <a href="{{ route('contact-guides.index') }}"
            class="inline-flex items-center gap-2 text-primary font-bold mb-4 hover:text-accent transition-colors">
            <i class="fas fa-arrow-right"></i> رجوع لدليل الاتصال
        </a>

        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="bg-primary p-6 text-white">
                <h3 class="text-2xl font-black flex items-center gap-3">
                    <i class="fas fa-address-book text-accent"></i> إضافة جهة اتصال جديدة
                </h3>
            </div>

            <form method="POST" action="{{ route('contact-guides.store') }}" class="p-8 space-y-6">
                @csrf

                @if ($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="space-y-1">
                    <label class="block text-xs font-black text-slate-600">اسم الإدارة <span class="text-red-500">*</span></label>
                    <input type="text" name="department_name" value="{{ old('department_name') }}" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none"
                        placeholder="مثال: الإدارة العامة للعمليات">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-black text-slate-600">مدير الإدارة</label>
                    <input type="text" name="manager_name" value="{{ old('manager_name') }}"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none"
                        placeholder="مثال: أ / محمد أحمد">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <label class="block text-xs font-black text-slate-600">رقم الهاتف</label>
                        <input type="text" name="phone_number" value="{{ old('phone_number') }}" dir="ltr"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none text-left"
                            placeholder="01xxxxxxxx">
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-black text-slate-600">رقم الأرضي</label>
                        <input type="text" name="landline_number" value="{{ old('landline_number') }}" dir="ltr"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none text-left"
                            placeholder="02xxxxxxxx">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-black text-slate-600">رقم هاتف إضافي</label>
                    <input type="text" name="additional_phone" value="{{ old('additional_phone') }}" dir="ltr"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none text-left"
                        placeholder="01xxxxxxxx">
                </div>

                <div class="pt-6 border-t border-slate-100">
                    <button type="submit"
                        class="w-full bg-primary text-white font-black py-4 rounded-2xl border-2 border-transparent hover:border-accent shadow-lg text-lg transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-save text-accent"></i> حفظ جهة الاتصال
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection