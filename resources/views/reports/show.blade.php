@extends('layouts.app')

@section('title', 'تفاصيل البلاغ ' . $report->REPORT_REGISTER_NUMBER)
@section('page-title', 'تفاصيل البلاغ')

@section('content')
    <div class="max-w-5xl mx-auto w-full space-y-6">

        <div class="bg-primary text-white rounded-3xl p-6 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-black">بلاغ رقم {{ $report->REPORT_REGISTER_NUMBER }}</h2>
                <p class="text-slate-300 text-sm">{{ $report->REPORT_START_DATE }} - {{ $report->REPORT_START_TIME }}</p>
            </div>
            <span class="px-4 py-2 bg-white/10 rounded-full font-bold">{{ $report->REQUEST_STATUS }}</span>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><span class="text-slate-400 text-sm block">اسم المبلغ</span><span class="font-bold">{{ $report->REPORTER_NAME }}</span></div>
            <div><span class="text-slate-400 text-sm block">الرقم القومي</span><span class="font-bold">{{ $report->REPORTER_SSN }}</span></div>
            <div><span class="text-slate-400 text-sm block">رقم الهاتف</span><span class="font-bold">{{ $report->REPORT_FOLLOWUP_NUMBER }}</span></div>
            <div><span class="text-slate-400 text-sm block">جهة البلاغ</span><span class="font-bold">{{ $report->REPORTING_Auth }}</span></div>
            <div><span class="text-slate-400 text-sm block">نوع البلاغ</span><span class="font-bold">{{ $report->reportingType->REPORT_SORT ?? '-' }}</span></div>
            <div><span class="text-slate-400 text-sm block">المركز</span><span class="font-bold">{{ $report->city->CITY_NAME ?? '-' }}</span></div>
            <div><span class="text-slate-400 text-sm block">المدينة/القرية</span><span class="font-bold">{{ $report->village->VILLAGE_NAME ?? '-' }}</span></div>
            <div class="md:col-span-2"><span class="text-slate-400 text-sm block">مكان الحادث</span><span class="font-bold">{{ $report->PLACE_Accident }}</span></div>
            <div class="md:col-span-2"><span class="text-slate-400 text-sm block">ملخص البلاغ</span><p class="font-bold whitespace-pre-line">{{ $report->DAMAGE }}</p></div>
        </div>

        @if ($report->injuries->isNotEmpty())
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                <h3 class="font-black text-primary mb-4"><i class="fas fa-user-injured text-blue-500 ml-2"></i>المصابون ({{ $report->injuries->count() }})</h3>
                <table class="w-full text-right text-sm">
                    <thead class="text-slate-400"><tr><th class="py-2">الاسم</th><th class="py-2">تاريخ الميلاد</th><th class="py-2">التشخيص</th><th class="py-2">المتابعة</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($report->injuries as $injured)
                            <tr>
                                <td class="py-2">{{ $injured->INJURED_NAME }}</td>
                                <td class="py-2">{{ $injured->INJURED_AGE }}</td>
                                <td class="py-2">{{ $injured->INJURED_DIAGNOSIS }}</td>
                                <td class="py-2">{{ $injured->INJURED_FOLLOWUP }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($report->deaths->isNotEmpty())
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                <h3 class="font-black text-primary mb-4"><i class="fas fa-cross text-red-500 ml-2"></i>المتوفون ({{ $report->deaths->count() }})</h3>
                <table class="w-full text-right text-sm">
                    <thead class="text-slate-400"><tr><th class="py-2">الاسم</th><th class="py-2">تاريخ الميلاد</th><th class="py-2">العنوان</th><th class="py-2">المتابعة</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($report->deaths as $deceased)
                            <tr>
                                <td class="py-2">{{ $deceased->Deceased_NAME }}</td>
                                <td class="py-2">{{ $deceased->Deceased_AGE }}</td>
                                <td class="py-2">{{ $deceased->Deceased_ADDRESS }}</td>
                                <td class="py-2">{{ $deceased->Deceased_FOLLOWUP }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div id="attachments" class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-black text-primary"><i class="fas fa-paperclip text-purple-500 ml-2"></i>المرفقات ({{ $report->attachments->count() }})</h3>

            </div>

            @forelse ($report->attachments as $attachment)
                <a href="{{ Storage::url($attachment->FilePath) }}" target="_blank"
                    class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 border border-slate-100 mb-2">
                    <i class="fas fa-file text-slate-400 text-xl"></i>
                    <span class="font-bold text-slate-700">{{ $attachment->AttachmentName }}</span>
                    <span class="text-slate-400 text-sm mr-auto">.{{ $attachment->FileExtension }}</span>
                </a>
            @empty
                <p class="text-slate-400 text-center py-4">لا توجد مرفقات لهذا البلاغ</p>
            @endforelse
        </div>

        <div class="flex gap-3">

            <a href="{{ route('reports.index') }}"
                class="flex-1 text-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3 rounded-xl">رجوع للقائمة</a>
        </div>
    </div>
@endsection
