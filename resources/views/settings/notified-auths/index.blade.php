@extends('layouts.app')

@section('title', 'جهات الإخطار - الإعدادات')
@section('page-title', 'جهات الإخطار')

@section('content')
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <h1 class="text-2xl font-black text-primary">جهات الإخطار</h1>
        @can('reports.create')
            <a href="{{ route('settings.notified-auths.create') }}"
                class="bg-accent hover:bg-accent-hover text-white font-bold px-5 py-3 rounded-xl transition-all flex items-center gap-2">
                <i class="fas fa-plus"></i> إضافة جهة إخطار
            </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm font-bold">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead class="bg-slate-50 text-slate-500 text-sm">
                    <tr>
                        <th class="px-4 py-4 font-bold">#</th>
                        <th class="px-4 py-4 font-bold">اسم الجهة</th>
                        <th class="px-4 py-4 font-bold text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($notifiedAuths as $notifiedAuth)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-4 text-slate-400">{{ $notifiedAuth->ID }}</td>
                            <td class="px-4 py-4 font-bold text-primary">{{ $notifiedAuth->Notified_Auth ?: '-' }}</td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @can('reports.edit')
                                        <a href="{{ route('settings.notified-auths.edit', $notifiedAuth) }}" title="تعديل"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100">
                                            <i class="fas fa-edit text-sm"></i>
                                        </a>
                                    @endcan
                                    @can('reports.delete')
                                        <form method="POST" action="{{ route('settings.notified-auths.destroy', $notifiedAuth) }}"
                                            onsubmit="return confirm('هل أنت متأكد من حذف جهة الإخطار؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="حذف"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100">
                                                <i class="fas fa-trash text-sm"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-slate-400">لا توجد جهات إخطار مسجلة</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $notifiedAuths->links() }}</div>
@endsection