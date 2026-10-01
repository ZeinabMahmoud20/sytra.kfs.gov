@extends('layouts.app')

@section('title', 'تعديل مجموعة جهات إشارة - الإعدادات')
@section('page-title', 'تعديل مجموعة جهات إشارة')

@section('content')
    <div class="max-w-3xl mx-auto w-full">
        <a href="{{ route('settings.signal-authority-groups.index') }}"
            class="inline-flex items-center gap-2 text-primary font-bold mb-4 hover:text-accent transition-colors">
            <i class="fas fa-arrow-right"></i> رجوع لمجموعات جهات الإشارة
        </a>

        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="bg-primary p-6 text-white flex items-center justify-between">
                <h3 class="text-2xl font-black flex items-center gap-3">
                    <i class="fas fa-layer-group text-accent"></i> تعديل مجموعة جهات إشارة
                </h3>
                <span class="text-sm font-bold opacity-80">{{ $group->GROUP_NAME }}</span>
            </div>

            <form method="POST" action="{{ route('settings.signal-authority-groups.update', $group) }}" class="p-8 space-y-6">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                @include('settings.signal-authority-groups._fields', [
                    'formId' => 'edit',
                    'groupName' => $group->GROUP_NAME,
                ])

                <div class="pt-6 border-t border-slate-100">
                    <button type="submit"
                        class="w-full bg-primary text-white font-black py-4 rounded-2xl border-2 border-transparent hover:border-accent shadow-lg text-lg transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-save text-accent"></i> حفظ التعديلات
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
