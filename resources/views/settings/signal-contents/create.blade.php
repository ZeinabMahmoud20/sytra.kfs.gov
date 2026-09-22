@extends('layouts.app')

@section('title', 'إضافة مضمون إشارة - الإعدادات')
@section('page-title', 'إضافة مضمون إشارة جديد')

@section('content')
    <div class="max-w-2xl mx-auto w-full">
        <a href="{{ route('settings.signal-contents.index') }}"
            class="inline-flex items-center gap-2 text-primary font-bold mb-4 hover:text-accent transition-colors">
            <i class="fas fa-arrow-right"></i> رجوع لمضمون الإشارة
        </a>

        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="bg-primary p-6 text-white">
                <h3 class="text-2xl font-black flex items-center gap-3">
                    <i class="fas fa-file-signature text-accent"></i> إضافة مضمون إشارة جديد
                </h3>
            </div>

            <form method="POST" action="{{ route('settings.signal-contents.store') }}" class="p-8 space-y-6">
                @csrf

                @if ($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="space-y-1">
                    <label class="block text-xs font-black text-slate-600">مضمون الإشارة <span class="text-red-500">*</span></label>
                    <input type="text" name="SIGNALCONTENT" value="{{ old('SIGNALCONTENT') }}" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none"
                        placeholder="مثال: حادث طريق - تصادم سيارتين">
                </div>

                <div class="pt-6 border-t border-slate-100">
                    <button type="submit"
                        class="w-full bg-primary text-white font-black py-4 rounded-2xl border-2 border-transparent hover:border-accent shadow-lg text-lg transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-save text-accent"></i> حفظ المضمون
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection