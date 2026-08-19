@extends('layouts.app')

@section('title', 'إدارة الصلاحيات')
@section('page-title', 'إدارة الصلاحيات')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3@extends('layouts.app')

@section('title', 'إدارة الصلاحيات')
@section('page-title', 'إدارة الصلاحيات')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <h1 class="text-2xl font-black text-primary">كل الصلاحيات المتاحة</h1>

            @foreach ($permissions as $module => $modulePermissions)
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <h3 class="font-black text-primary mb-4 border-r-4 border-accent pr-3">{{ module_label($module) }}</h3>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($modulePermissions as $permission)
                            <div class="flex items-center gap-2 bg-slate-50 px-3 py-2 rounded-lg border border-slate-200"
                                title="{{ $permission->name }}">
                                <span class="text-sm font-bold text-slate-700">{{ permission_label($permission->name) }}</span>
                                <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}"
                                    onsubmit="return confirm('متأكد من حذف الصلاحية دي؟ هتتشال من كل الأدوار المرتبطة بيها.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-600 text-xs">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div>
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 sticky top-24">
                <h3 class="font-black text-primary mb-4"><i class="fas fa-plus-circle text-accent ml-2"></i>إضافة صلاحية جديدة</h3>

                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.permissions.store') }}" class="space-y-4">
                    @csrf
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-500">اسم الصلاحية</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="module.action مثال: signals.approve"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none" dir="ltr">
                        <p class="text-xs text-slate-400">استخدم حروف إنجليزية صغيرة ونقطة واحدة فقط، مثل: reports.approve</p>
                    </div>
                    <button type="submit" class="w-full bg-accent hover:bg-accent-hover text-white font-black py-3 rounded-xl">
                        إضافة الصلاحية
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection gap-6">
        <div class="lg:col-span-2 space-y-6">
            <h1 class="text-2xl font-black text-primary">كل الصلاحيات المتاحة</h1>

            @foreach ($permissions as $module => $modulePermissions)
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <h3 class="font-black text-primary mb-4 border-r-4 border-accent pr-3">{{ $module }}</h3>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($modulePermissions as $permission)
                            <div class="flex items-center gap-2 bg-slate-50 px-3 py-2 rounded-lg border border-slate-200">
                                <span class="text-sm font-bold text-slate-700">{{ $permission->name }}</span>
                                <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}"
                                    onsubmit="return confirm('هل انت متأكد من حذف هذه الصلاحية؟ سيتم حذفها من جميع الأدوار المرتبطة بها.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-600 text-xs">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div>
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 sticky top-24">
                <h3 class="font-black text-primary mb-4"><i class="fas fa-plus-circle text-accent ml-2"></i>إضافة صلاحية جديدة</h3>

                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.permissions.store') }}" class="space-y-4">
                    @csrf
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-500">اسم الصلاحية</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="module.action مثال: signals.approve"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none" dir="ltr">
                        <p class="text-xs text-slate-400">استخدم حروف إنجليزية صغيرة ونقطة واحدة فقط، مثل: reports.approve</p>
                    </div>
                    <button type="submit" class="w-full bg-accent hover:bg-accent-hover text-white font-black py-3 rounded-xl">
                        إضافة الصلاحية
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
