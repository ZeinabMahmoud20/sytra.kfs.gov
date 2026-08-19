@extends('layouts.app')

@section('title', 'قائمة التمامات - نظام التمامات')
@section('page-title', 'قائمة التمامات')

@section('content')
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <h1 class="text-2xl font-black text-primary">كل التمامات</h1>
        @can('tmam.create')
            <a href="{{ route('attendance-templates.create') }}"
                class="bg-accent hover:bg-accent-hover text-white font-bold px-5 py-3 rounded-xl transition-all flex items-center gap-2">
                <i class="fas fa-plus"></i> إضافة تمام جديد
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
                        <th class="px-4 py-4 font-bold">اسم التمام</th>
                        <th class="px-4 py-4 font-bold">موعد التمام</th>
                        <th class="px-4 py-4 font-bold text-center">عدد الجهات المرتبطة</th>
                        <th class="px-4 py-4 font-bold text-center">الجهات يوميًا</th>
                        <th class="px-4 py-4 font-bold text-center">الحالة</th>
                        <th class="px-4 py-4 font-bold text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($templates as $template)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-4 font-bold text-primary">{{ $template->name }}</td>
                            <td class="px-4 py-4 text-slate-500">{{ $template->attendance_time->format('h:i A') }}</td>
                            <td class="px-4 py-4 text-center text-slate-500">{{ $template->entities_count }}</td>
                            <td class="px-4 py-4 text-center text-slate-500">{{ $template->daily_entities_count }}</td>
                            <td class="px-4 py-4 text-center">
                                @if ($template->is_active)
                                    <span class="px-3 py-1 rounded-full text-xs font-black bg-green-100 text-green-600">نشط</span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-black bg-slate-100 text-slate-500">متوقف</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @can('tmam.edit')
                                        <a href="{{ route('attendance-templates.edit', $template) }}" title="تعديل"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100">
                                            <i class="fas fa-edit text-sm"></i>
                                        </a>
                                    @endcan
                                    @can('tmam.delete')
                                        <form method="POST" action="{{ route('attendance-templates.destroy', $template) }}"
                                            onsubmit="return confirm('متأكد إنك عايز تحذف هذا التمام؟ هيتم حذف كل السجلات المرتبطة بيه كمان.')">
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
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">لا توجد تمامات مسجلة</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $templates->links() }}</div>
@endsection