@extends('layouts.app')

@section('title', 'إضافة دور جديد')
@section('page-title', 'إضافة دور جديد')

@section('content')
    <div class="max-w-3xl mx-auto w-full">
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="bg-primary p-6 text-white">
                <h3 class="text-2xl font-black">إضافة دور جديد</h3>
            </div>

            <form method="POST" action="{{ route('admin.roles.store') }}" class="p-8 space-y-8">
                @csrf

                @if ($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="space-y-2">
                    <label class="block text-sm font-bold text-slate-500">اسم الدور</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="مثال: موظف إشارات"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                </div>

                <div class="space-y-3">
                    <h4 class="font-black text-primary border-r-4 border-accent pr-3">الصلاحيات المرتبطة بالدور</h4>
                    @include('admin.roles._permissions-checklist', ['checkedPermissions' => old('permissions', [])])
                </div>

                <div class="flex gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="flex-1 bg-primary text-white font-black py-3 rounded-xl">حفظ الدور</button>
                    <a href="{{ route('admin.roles.index') }}" class="flex-1 text-center bg-slate-100 text-slate-700 font-bold py-3 rounded-xl">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
@endsection
