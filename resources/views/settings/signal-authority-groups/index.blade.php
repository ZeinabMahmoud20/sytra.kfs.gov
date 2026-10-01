@extends('layouts.app')

@section('title', 'مجموعات جهات الإشارة - الإعدادات')
@section('page-title', 'مجموعات جهات الإشارة')

@section('content')
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <h1 class="text-2xl font-black text-primary">مجموعات جهات الإشارة</h1>
        @can('signals.create')
            <a href="{{ route('settings.signal-authority-groups.create') }}"
                class="bg-accent hover:bg-accent-hover text-white font-bold px-5 py-3 rounded-xl transition-all flex items-center gap-2">
                <i class="fas fa-plus"></i> إضافة مجموعة
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
                        <th class="px-4 py-4 font-bold">اسم المجموعة</th>
                        <th class="px-4 py-4 font-bold">عدد الجهات</th>
                        <th class="px-4 py-4 font-bold">الجهات الأعضاء</th>
                        <th class="px-4 py-4 font-bold text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($groups as $group)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-4 text-slate-400">{{ $group->ID }}</td>
                            <td class="px-4 py-4 font-bold text-primary">
                                <span class="inline-flex items-center gap-2">
                                    <i class="fas fa-layer-group text-accent"></i>
                                    {{ $group->GROUP_NAME ?: '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-slate-600">{{ $group->authorities_count }}</td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($group->authorities as $authority)
                                        <span class="px-2 py-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-bold">
                                            {{ $authority->SIGNAL_NAME }}
                                        </span>
                                    @empty
                                        <span class="text-slate-400 text-xs">لا توجد جهات</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @can('signals.edit')
                                        <a href="{{ route('settings.signal-authority-groups.edit', $group) }}" title="تعديل"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100">
                                            <i class="fas fa-edit text-sm"></i>
                                        </a>
                                    @endcan
                                    @can('signals.delete')
                                        <form method="POST" action="{{ route('settings.signal-authority-groups.destroy', $group) }}"
                                            onsubmit="return confirm('هل أنت متأكد من حذف هذه المجموعة؟')">
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
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                لا توجد مجموعات جهات إشارة مسجلة
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $groups->links() }}</div>
@endsection
