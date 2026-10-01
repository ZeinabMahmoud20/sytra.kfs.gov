@extends('layouts.app')

@section('title', 'إضافة مجموعة جهات إشارة - الإعدادات')
@section('page-title', 'إضافة مجموعة جهات إشارة جديدة')

@section('content')
    <div class="max-w-3xl mx-auto w-full">
        <a href="{{ route('settings.signal-authority-groups.index') }}"
            class="inline-flex items-center gap-2 text-primary font-bold mb-4 hover:text-accent transition-colors">
            <i class="fas fa-arrow-right"></i> رجوع لمجموعات جهات الإشارة
        </a>

        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="bg-primary p-6 text-white">
                <h3 class="text-2xl font-black flex items-center gap-3">
                    <i class="fas fa-layer-group text-accent"></i> إضافة مجموعة جهات إشارة جديدة
                </h3>
            </div>

            <form method="POST" action="{{ route('settings.signal-authority-groups.store') }}" class="p-8 space-y-6">
                @csrf

                @if ($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                @include('settings.signal-authority-groups._fields', ['formId' => 'create'])

                <div class="pt-6 border-t border-slate-100">
                    <button type="submit"
                        class="w-full bg-primary text-white font-black py-4 rounded-2xl border-2 border-transparent hover:border-accent shadow-lg text-lg transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-save text-accent"></i> حفظ المجموعة
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
