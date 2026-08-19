@extends('layouts.app')

@section('title', 'إضافة موظف جديد')
@section('page-title', 'إضافة موظف جديد')

@section('content')
    <div class="max-w-2xl mx-auto w-full">
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="bg-primary p-6 text-white">
                <h3 class="text-2xl font-black">إضافة حساب موظف جديد</h3>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" class="p-8 space-y-6">
                @csrf

                @if ($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-500">الاسم الظاهر (اسم الدخول)</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-500">البريد الإلكتروني</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none" dir="ltr">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-500">كلمة المرور</label>
                        <input type="password" name="password" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-500">تأكيد كلمة المرور</label>
                        <input type="password" name="password_confirmation" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-500">الاسم الكامل (اختياري)</label>
                        <input type="text" name="employee_full_name" value="{{ old('employee_full_name') }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-500">رقم الهاتف (اختياري)</label>
                        <input type="text" name="phone_number" value="{{ old('phone_number') }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                    </div>
                    <div class="space-y-2 md:col-span-2">
                        <label class="block text-sm font-bold text-slate-500">الوظيفة (اختياري)</label>
                        <input type="text" name="job_title" value="{{ old('job_title') }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none">
                    </div>
                </div>

                <div class="space-y-3">
                    <h4 class="font-black text-primary border-r-4 border-accent pr-3">الأدوار المطلوب تعيينها</h4>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($roles as $role)
                            <label class="flex items-center gap-2 cursor-pointer bg-slate-50 px-4 py-2 rounded-lg border border-slate-200 hover:border-accent">
                                <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="w-4 h-4 accent-accent"
                                    @checked(in_array($role->name, old('roles', [])))>
                                <span class="font-bold text-sm">{{ $role->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="flex-1 bg-primary text-white font-black py-3 rounded-xl">إنشاء الحساب</button>
                    <a href="{{ route('admin.users.index') }}" class="flex-1 text-center bg-slate-100 text-slate-700 font-bold py-3 rounded-xl">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
@endsection
