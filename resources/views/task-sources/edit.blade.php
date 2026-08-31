@extends('layouts.app')

@section('title', 'تعديل مصدر تكليف - متابعة التكليفات')
@section('page-title', 'تعديل مصدر التكليف')

@section('content')
    <div class="max-w-2xl mx-auto w-full">
        <a href="{{ route('task-sources.index') }}"
            class="inline-flex items-center gap-2 text-primary font-bold mb-4 hover:text-accent transition-colors">
            <i class="fas fa-arrow-right"></i> رجوع لقائمة مصادر التكليف
        </a>

        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="bg-primary p-6 text-white">
                <h3 class="text-2xl font-black flex items-center gap-3">
                    <i class="fas fa-share-nodes text-accent"></i> تعديل مصدر التكليف
                </h3>
            </div>

            <form method="POST" action="{{ route('task-sources.update', $source) }}" class="p-8 space-y-6">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="space-y-1">
                    <label class="block text-xs font-black text-slate-600">اسم المصدر <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $source->name) }}" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                </div>

                <div class="flex items-center gap-3">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="is_active"
                        {{ old('is_active', $source->is_active) ? 'checked' : '' }}
                        class="w-5 h-5 rounded border-slate-300 text-accent focus:ring-accent">
                    <label for="is_active" class="text-sm font-bold text-slate-600">مفعّل</label>
                </div>

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
