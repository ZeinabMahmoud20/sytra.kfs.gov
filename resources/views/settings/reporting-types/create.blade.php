@extends('layouts.app')

@section('title', 'إضافة نوع بلاغ - الإعدادات')
@section('page-title', 'إضافة نوع بلاغ جديد')

@section('content')
    <div class="max-w-2xl mx-auto w-full">
        <a href="{{ route('settings.reporting-types.index') }}"
            class="inline-flex items-center gap-2 text-primary font-bold mb-4 hover:text-accent transition-colors">
            <i class="fas fa-arrow-right"></i> رجوع لأنواع البلاغات
        </a>

        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="bg-primary p-6 text-white">
                <h3 class="text-2xl font-black flex items-center gap-3">
                    <i class="fas fa-file-circle-plus text-accent"></i> إضافة نوع بلاغ جديد
                </h3>
            </div>

            <form method="POST" action="{{ route('settings.reporting-types.store') }}" class="p-8 space-y-6">
                @csrf

                @if ($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="space-y-1">
                    <label class="block text-xs font-black text-slate-600">نوع البلاغ <span class="text-red-500">*</span></label>
                    <input type="text" name="REPORT_SORT" value="{{ old('REPORT_SORT') }}" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none"
                        placeholder="مثال: حريق">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-black text-slate-600">جهة البلاغ <span class="text-red-500">*</span></label>
                    <input type="text" name="AUTHORITY" value="{{ old('AUTHORITY') }}" required list="authorities-list"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none"
                        placeholder="مثال: الحماية المدنية">
                    <datalist id="authorities-list">
                        @foreach ($authorities as $authority)
                            <option value="{{ $authority }}"></option>
                        @endforeach
                    </datalist>
                    <p class="text-xs text-slate-400 mt-1">يمكن اختيار جهة موجودة أو كتابة جهة جديدة تظهر تلقائياً في قوائم البلاغات</p>
                </div>

                <div class="flex items-center gap-3">
                    <input type="hidden" name="IS_INTERNET" value="0">
                    <input type="checkbox" name="IS_INTERNET" value="1" id="IS_INTERNET"
                        {{ old('IS_INTERNET') ? 'checked' : '' }}
                        class="w-5 h-5 rounded border-slate-300 text-accent focus:ring-accent">
                    <label for="IS_INTERNET" class="text-sm font-bold text-slate-600">بلاغ عبر الإنترنت</label>
                </div>

                <div class="pt-6 border-t border-slate-100">
                    <button type="submit"
                        class="w-full bg-primary text-white font-black py-4 rounded-2xl border-2 border-transparent hover:border-accent shadow-lg text-lg transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-save text-accent"></i> حفظ نوع البلاغ
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection