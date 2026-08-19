@extends('layouts.app')

@section('title', 'قائمة الجهات - نظام التمامات')
@section('page-title', 'قائمة الجهات')

@section('content')
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <h1 class="text-2xl font-black text-primary">كل الجهات</h1>
        <div class="flex items-center gap-3 flex-wrap">
            @can('tmam.import')
                <a href="{{ route('entities.import.form') }}"
                    class="bg-green-600 hover:bg-green-700 text-white font-bold px-4 py-3 rounded-xl transition-all flex items-center gap-2 text-sm">
                    <i class="fas fa-file-excel"></i> رفع من Excel
                </a>
            @endcan
            @can('tmam.create')
                <a href="{{ route('entities.create') }}"
                    class="bg-accent hover:bg-accent-hover text-white font-bold px-5 py-3 rounded-xl transition-all flex items-center gap-2">
                    <i class="fas fa-plus"></i> إضافة جهة جديدة
                </a>
            @endcan
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm font-bold">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-700 text-sm font-bold">
            <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
        </div>
    @endif

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead class="bg-slate-50 text-slate-500 text-sm">
                    <tr>
                        <th class="px-4 py-4 font-bold">#</th>
                        <th class="px-4 py-4 font-bold">القرية</th>
                        <th class="px-4 py-4 font-bold">المكان</th>
                        <th class="px-4 py-4 font-bold text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($entities as $entity)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-4 text-slate-400">{{ $entity->id }}</td>
                            <td class="px-4 py-4 font-bold text-primary">{{ $entity->name }}</td>
                            <td class="px-4 py-4 text-slate-600">{{ $entity->main_location ?? '—' }}</td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @can('tmam.edit')
                                        <a href="{{ route('entities.edit', $entity) }}" title="تعديل"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100">
                                            <i class="fas fa-edit text-sm"></i>
                                        </a>
                                    @endcan
                                    @can('tmam.delete')
                                        <form method="POST" action="{{ route('entities.destroy', $entity) }}"
                                            onsubmit="return confirm('هل انت متأكد من حذف الجهة؟')">
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
                            <td colspan="4" class="px-6 py-8 text-center text-slate-400">لا توجد جهات مسجلة</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $entities->links() }}</div>
@endsection