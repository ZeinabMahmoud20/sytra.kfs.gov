<?php

namespace App\Http\Controllers;

use App\Exports\ReportsExport;
use App\Models\Attachment;
use App\Models\City;
use App\Models\Death;
use App\Models\Injured;
use App\Models\NotifiedAuth;
use App\Models\RecieveReport;
use App\Models\ReportAuth;
use App\Models\ReportingType;
use App\Models\SystemRecord;
use App\Models\Village;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    /**
     * قائمة الحالات الممكنة للبلاغ - نفس القيم المستخدمة وقت الحفظ.
     * "تم الانتهاء" حالة خاصة (قفل البلاغ) ومسموح بيها للمشرف العام / الادمن فقط
     * (شوف canLockReport في edit() و update()).
     */
    protected array $statuses = [
        'تم استلام البلاغ',
        'قيد المعالجة',
        'تم التنفيذ',
        'تم الانتهاء',
    ];

    public function index(Request $request)
    {
        $reports = $this->filteredQuery($request)->paginate(20)->withQueryString();

        return view('reports.index', [
            'reports' => $reports,
            'cities' => City::orderBy('CITY_NAME')->get(),
            'authorities' => ReportingType::where('IS_INTERNET', false)->whereNotNull('AUTHORITY')->distinct()->pluck('AUTHORITY'),
            'reportingTypes' => ReportingType::orderBy('REPORT_SORT')->get(),
            'statuses' => $this->statuses,
        ]);

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                $q->where('REPORT_REGISTER_NUMBER', 'like', "%{$search}%")
                    ->orWhere('REPORTER_NAME', 'like', "%{$search}%")
                    ->orWhere('REPORTER_SSN', 'like', "%{$search}%")
                    ->orWhere('REPORT_FOLLOWUP_NUMBER', 'like', "%{$search}%")
                    ->orWhere('REQUEST_STATUS', 'like', "%{$search}%");
            });
        }
    }

    /**
     * الكويري المشترك بين عرض القائمة والتصدير (Excel/PDF)، عشان الفلاتر
     * تفضل نفسها في الحالتين من غير تكرار كود.
     */
    protected function filteredQuery(Request $request)
    {
        // المدينة والقرية متبادلين مش مع بعض (زي الديسكتوب): لو الاتنين اتبعتوا
        // بنفضّل "القرية" لو موجودة، وإلا "المدينة"
        $villageFilter = $request->filled('village') ? $request->village : $request->input('madina');

        return RecieveReport::query()
            ->with(['reportingType', 'city', 'village', 'user'])
            ->when($request->filled('reporter_name'), fn($q) => $q->where('REPORTER_NAME', 'like', "%{$request->reporter_name}%"))
            ->when($request->filled('national_id'), fn($q) => $q->where('REPORTER_SSN', 'like', "%{$request->national_id}%"))
            ->when($request->filled('phone'), fn($q) => $q->where('REPORT_FOLLOWUP_NUMBER', 'like', "%{$request->phone}%"))
            ->when($request->filled('city'), fn($q) => $q->where('CITY', $request->city))
            ->when($villageFilter, fn($q) => $q->where('VILLAGE', $villageFilter))
            // جهة البلاغ: بتتطابق مع REPORTING_Auth المباشر أو AUTHORITY بتاعة نوع البلاغ (زي الديسكتوب بالظبط)
            ->when($request->filled('reporting_auth'), function ($q) use ($request) {
                $auth = $request->reporting_auth;
                $q->where(function ($sub) use ($auth) {
                    $sub->where('REPORTING_Auth', $auth)
                        ->orWhereHas('reportingType', fn($rt) => $rt->where('AUTHORITY', $auth));
                });
            })
            ->when($request->filled('reporting_sort'), fn($q) => $q->where('REPORTING_SORT', $request->reporting_sort))
            ->when($request->filled('status'), fn($q) => $q->where('REQUEST_STATUS', $request->status))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('REPORT_START_DATE', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('REPORT_START_DATE', '<=', $request->date_to))
            ->when($request->filled('time_from') && $request->boolean('filter_by_time'), fn($q) => $q->whereTime('REPORT_START_TIME', '>=', $request->time_from))
            ->when($request->filled('time_to') && $request->boolean('filter_by_time'), fn($q) => $q->whereTime('REPORT_START_TIME', '<=', $request->time_to))
            ->latest('ID');
    }

    /**
     * عرض تفاصيل بلاغ واحد بكل مرفقاته وبياناته الفرعية.
     */
    public function show(RecieveReport $report)
    {
        $report->load(['reportingType', 'city', 'village', 'deaths', 'injuries', 'attachments', 'reportAuths', 'lockedByUser']);

        return view('reports.show', compact('report'));
    }

    /**
     * تعديل البيانات الأساسية للبلاغ (مش شامل المصابين/المتوفين حالياً).
     */
    public function edit(RecieveReport $report)
    {
        // المشرف العام / الادمن بس اللي يقدر يشوف/يختار حالة "تم الانتهاء" (قفل البلاغ)
        $canLockReport = auth()->user()->hasAnyRole(['مشرف عام', 'ادمن']);

        // المشرف العام بس اللي يقدر يعدّل بلاغ "تم الانتهاء"، أي دور تاني يبقى عرض فقط
        $isLocked = $report->REQUEST_STATUS === 'تم الانتهاء' && !$canLockReport;

        $report->load('lockedByUser');

        return view('reports.edit', [
            'report' => $report,
            'isLocked' => $isLocked,
            'canLockReport' => $canLockReport,
            'canEditDateTime' => auth()->user()->hasRole('مشرف عام'),
            'authorities' => ReportingType::where('IS_INTERNET', false)->whereNotNull('AUTHORITY')->distinct()->pluck('AUTHORITY'),
            'reportingTypes' => ReportingType::where('AUTHORITY', $report->REPORTING_Auth)->get(),
            'cities' => City::orderBy('CITY_NAME')->get(),
            'villages' => Village::where('CITY_ID', $report->CITY)->orderBy('VILLAGE_NAME')->get(),
            'statuses' => $this->statuses,
            'notifiedAuthorities' => NotifiedAuth::orderBy('Notified_Auth')->pluck('Notified_Auth'),
            'selectedAuthorities' => $report->reportAuths()->pluck('AUTHORITY_ID')->toArray(),
        ]);
    }

    public function update(Request $request, RecieveReport $report)
    {
        $canLockReport = auth()->user()->hasAnyRole(['مشرف عام', 'ادمن']);

        if ($report->REQUEST_STATUS === 'تم الانتهاء' && !$canLockReport) {
            abort(403, 'لا يمكن تعديل بلاغ تم الانتهاء منه');
        }

        // الحالات المسموح اختيارها في الفورم: "تم الانتهاء" (قفل البلاغ) للمشرف العام/الادمن بس
        $allowedStatuses = $canLockReport
            ? $this->statuses
            : array_values(array_diff($this->statuses, ['تم الانتهاء']));

        $validated = $request->validate([
            'REPORTER_NAME' => ['required', 'string', 'max:50'],
            'REPORT_FOLLOWUP_NUMBER' => ['required', 'regex:/^(01[0125][0-9]{8}|0[0-9]{9})$/'],
            'REPORTER_SSN' => ['required', 'digits:14', 'regex:/^[23]\d{13}$/'],
            'REPORT_START_DATE' => ['required', 'date'],
            'REPORT_START_TIME' => ['required'],
            'REPORTING_Auth' => ['required', 'string', 'max:500'],
            'REPORTING_SORT' => ['required', 'exists:REPORTING_TYPES,REPORT_ID'],
            'CITY' => ['required', 'exists:CITY,CITY_ID'],
            'location_type' => ['required', 'in:مدينة,قرية'],
            'VILLAGE' => ['required', 'exists:VILLAGE,VILLAGE_ID'],
            'X_AXIS' => ['nullable', 'numeric'],
            'Y_AXIS' => ['nullable', 'numeric'],
            'PLACE_Accident' => ['required', 'string', 'max:500'],
            'DAMAGE' => ['required', 'string'],
            'REQUEST_STATUS' => ['required', Rule::in($allowedStatuses)],
            'notified_authorities' => ['nullable', 'array'],
            'notified_authorities.*' => ['string'],
            'injured' => ['nullable', 'array'],
            'injured.*.name' => ['required_with:injured', 'string', 'max:50'],
            'injured.*.age' => ['required_with:injured', 'integer', 'min:1', 'max:150'],
            'injured.*.diagnosis' => ['nullable', 'string', 'max:500'],
            'injured.*.followup' => ['nullable', 'string'],
            'deceased' => ['nullable', 'array'],
            'deceased.*.name' => ['required_with:deceased', 'string', 'max:50'],
            'deceased.*.age' => ['required_with:deceased', 'integer', 'min:1', 'max:150'],
            'deceased.*.address' => ['nullable', 'string'],
            'deceased.*.followup' => ['nullable', 'string'],
        ]);

        $isAdmin = auth()->user()->hasRole('مشرف عام');
        $startDate = $isAdmin ? $validated['REPORT_START_DATE'] : $report->REPORT_START_DATE;
        $startTime = $isAdmin ? $validated['REPORT_START_TIME'] : $report->REPORT_START_TIME;

        // هل البلاغ كان مقفول قبل التعديل ده، وهل هيبقى مقفول بعده؟
        $wasLocked = $report->REQUEST_STATUS === 'تم الانتهاء';
        $willBeLocked = $validated['REQUEST_STATUS'] === 'تم الانتهاء';

        $oldData = $report->toArray();

        DB::transaction(function () use ($report, $validated, $startDate, $startTime, $wasLocked, $willBeLocked) {
            $reportData = [
                'REPORTER_SSN' => $validated['REPORTER_SSN'],
                'REPORTER_NAME' => $validated['REPORTER_NAME'],
                'REPORTING_Auth' => $validated['REPORTING_Auth'],
                'REPORTING_SORT' => $validated['REPORTING_SORT'],
                'REPORT_START_DATE' => $startDate,
                'REPORT_START_TIME' => $startTime,
                'CITY' => $validated['CITY'],
                'VILLAGE' => $validated['VILLAGE'],
                'X_AXIS' => $validated['X_AXIS'] ?? 0,
                'Y_AXIS' => $validated['Y_AXIS'] ?? 0,
                'DAMAGE' => $validated['DAMAGE'],
                'PLACE_Accident' => $validated['PLACE_Accident'],
                'Deceased_Num' => count($validated['deceased'] ?? []),
                'INFECTED_NUM' => count($validated['injured'] ?? []),
                'REQUEST_STATUS' => $validated['REQUEST_STATUS'],
                'REPORT_FOLLOWUP_NUMBER' => $validated['REPORT_FOLLOWUP_NUMBER'],
            ];

            if ($willBeLocked && !$wasLocked) {
                // أول مرة يتقفل فيها البلاغ: نسجل تاريخ ووقت القفل، ومين اللي قفله
                $reportData['REPORT_END_DATE'] = now()->format('Y-m-d');
                $reportData['REPORT_END_TIME'] = now()->format('H:i:s');
                $reportData['LOCKED_BY'] = auth()->id();
            } elseif (!$willBeLocked && $wasLocked) {
                // لو المشرف فتح البلاغ تاني بعد ما كان مقفول، نمسح بيانات القفل
                $reportData['REPORT_END_DATE'] = null;
                $reportData['REPORT_END_TIME'] = null;
                $reportData['LOCKED_BY'] = null;
            }

            $report->update($reportData);

            // إعادة بناء الجهات المخطرة
            $report->reportAuths()->delete();
            foreach ($validated['notified_authorities'] ?? [] as $authorityName) {
                ReportAuth::create([
                    'REPORT_ID' => $report->ID,
                    'AUTHORITY_ID' => $authorityName,
                ]);
            }

            // إعادة بناء المصابين
            $report->injuries()->delete();
            foreach ($validated['injured'] ?? [] as $injured) {
                Injured::create([
                    'INJURED_NAME' => $injured['name'],
                    'INJURED_AGE' => $injured['age'],
                    'INJURED_DIAGNOSIS' => $injured['diagnosis'] ?? null,
                    'INJURED_FOLLOWUP' => $injured['followup'] ?? null,
                    'REPORT_ID' => $report->ID,
                ]);
            }

            // إعادة بناء المتوفين
            $report->deaths()->delete();
            foreach ($validated['deceased'] ?? [] as $deceased) {
                Death::create([
                    'Deceased_NAME' => $deceased['name'],
                    'Deceased_AGE' => $deceased['age'],
                    'Deceased_ADDRESS' => $deceased['address'] ?? null,
                    'Deceased_FOLLOWUP' => $deceased['followup'] ?? null,
                    'REPORT_ID' => $report->ID,
                ]);
            }
        });

        SystemRecord::create([
            'USER_FULL_NAME' => auth()->user()->name,
            'DEVICE_NAME' => substr($request->userAgent() ?? 'غير معروف', 0, 255),
            'MACHINE_IP' => $request->ip(),
            'TITLE' => 'تعديل بلاغ',
            'DESCRIBTION' => "تم تعديل البلاغ رقم {$report->REPORT_REGISTER_NUMBER} بواسطة " . auth()->user()->name,
            'CREATED_DATE' => now()->format('Y-m-d'),
            'ISACTIVE' => '1',
            'USER_ID' => auth()->id(),
        ]);

        return redirect()->route('reports.index')->with('success', "تم تحديث البلاغ رقم {$report->REPORT_REGISTER_NUMBER} بنجاح");
    }

    /**
     * حذف البلاغ وكل البيانات المرتبطة بيه (مصابين، متوفين، مرفقات، جهات مخطرة).
     */
    public function destroy(RecieveReport $report)
    {
        DB::transaction(function () use ($report) {
            $report->injuries()->delete();
            $report->deaths()->delete();
            $report->reportAuths()->delete();

            foreach ($report->attachments as $attachment) {
                Storage::disk('public')->delete($attachment->FilePath);
                $attachment->delete();
            }

            $registerNumber = $report->REPORT_REGISTER_NUMBER;
            $report->delete();

            SystemRecord::create([
                'USER_FULL_NAME' => auth()->user()->name,
                'DEVICE_NAME' => substr(request()->userAgent() ?? 'غير معروف', 0, 255),
                'MACHINE_IP' => request()->ip(),
                'TITLE' => 'حذف بلاغ',
                'DESCRIBTION' => "تم حذف البلاغ رقم {$registerNumber} بواسطة " . auth()->user()->name,
                'CREATED_DATE' => now()->format('Y-m-d'),
                'ISACTIVE' => '1',
                'USER_ID' => auth()->id(),
            ]);
        });

        return redirect()->route('reports.index')->with('success', 'تم حذف البلاغ بنجاح');
    }

    /**
     * عرض فورم إضافة مرفق لبلاغ معين.
     */
    public function createAttachment(RecieveReport $report)
    {
        return view('reports.attachments-create', compact('report'));
    }

    /**
     * حفظ مرفق جديد للبلاغ.
     */
    public function storeAttachment(Request $request, RecieveReport $report)
    {
        $request->validate([
            'AttachmentName' => ['required', 'in:صورة البلاغ,صورة متابعة البلاغ'],
            'attachment' => ['required', 'file', 'max:10240'], // حد أقصى 10 ميجا
        ]);

        $file = $request->file('attachment');
        $path = $file->store('attachments', 'public');

        Attachment::create([
            'AttachmentName' => $request->AttachmentName,
            'ReportID' => $report->ID,
            'FilePath' => $path,
            'FileExtension' => $file->getClientOriginalExtension(),
        ]);

        return redirect()->route('reports.show', $report)->with('success', 'تم رفع المرفق بنجاح');
    }

    /**
     * تصدير النتائج (بعد تطبيق نفس الفلاتر) إلى ملف Excel.
     */
    public function exportExcel(Request $request)
    {
        $reports = $this->filteredQuery($request)->get();

        return \Maatwebsite\Excel\Facades\Excel::download(new ReportsExport($reports), 'البلاغات.xlsx');
    }

    /**
     * تصدير النتائج (بعد تطبيق نفس الفلاتر) إلى ملف PDF.
     */
    public function exportPdf(Request $request)
    {
        $reports = $this->filteredQuery($request)->get();

        // معالجة النصوص العربية (وصل الحروف ببعض) قبل ما تدخل الـ PDF
        $arabic = new \ArPHP\I18N\Arabic();

        $reshapedReports = $reports->map(function ($report) use ($arabic) {
            $clone = clone $report;
            $clone->REPORTER_NAME = $arabic->utf8Glyphs($report->REPORTER_NAME ?? '');
            $clone->REPORTING_Auth = $arabic->utf8Glyphs($report->REPORTING_Auth ?? '');
            $clone->PLACE_Accident = $arabic->utf8Glyphs($report->PLACE_Accident ?? '');
            $clone->REQUEST_STATUS = $arabic->utf8Glyphs($report->REQUEST_STATUS ?? '');

            if ($report->reportingType) {
                $clone->reportingType = clone $report->reportingType;
                $clone->reportingType->REPORT_SORT = $arabic->utf8Glyphs($report->reportingType->REPORT_SORT ?? '');
                $clone->reportingType->AUTHORITY = $arabic->utf8Glyphs($report->reportingType->AUTHORITY ?? '');
            }

            if ($report->city) {
                $clone->city = clone $report->city;
                $clone->city->CITY_NAME = $arabic->utf8Glyphs($report->city->CITY_NAME ?? '');
            }

            if ($report->village) {
                $clone->village = clone $report->village;
                $clone->village->VILLAGE_NAME = $arabic->utf8Glyphs($report->village->VILLAGE_NAME ?? '');
            }

            if ($report->user) {
                $clone->user = clone $report->user;
                $clone->user->name = $arabic->utf8Glyphs($report->user->name ?? '');
            }

            return $clone;
        });

        // نفس أعمدة صفحة البلاغات (من غير عمود "إجراءات").
        // الـ dompdf مش بيدعم RTL فيرسم الجداول من الشمال لليمين،
        // فبنعكس ترتيب الأعمدة هنا عشان العمود الأول يظهر يمين (من اليمين لليسار)
        $logicalHeaders = [
            'الرقم القومي',
            'رقم قيد البلاغ',
            'متلقي البلاغ',
            'اسم المبلغ',
            'جهة البلاغ',
            'نوع البلاغ',
            'المركز',
            'مكان البلاغ',
            'تاريخ تقديم البلاغ',
            'وقت تقديم البلاغ',
            'عدد المصابين',
            'عدد الوفيات',
            'حالة البلاغ',
            'تاريخ انتهاء البلاغ',
            'وقت انتهاء البلاغ',
            'رقم تليفون',
        ];

        $pdf = Pdf::loadView('reports.export-pdf', [
            'reports' => $reshapedReports,
            'pageTitle' => $arabic->utf8Glyphs('تقرير البلاغات - الشبكة الوطنية للطوارئ بكفر الشيخ'),
            'printedAtLabel' => $arabic->utf8Glyphs('تاريخ الطباعة:'),
            'countLabel' => $arabic->utf8Glyphs('عدد النتائج:'),
            'tableHeaders' => array_map(fn ($header) => $arabic->utf8Glyphs($header), array_reverse($logicalHeaders)),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('البلاغات.pdf');
    }

    // -------------------------------------------------------------
    // نفس الميثودز اللي كانت موجودة قبل كده (بدون تعديل في المنطق)
    // -------------------------------------------------------------

    public function create()
    {
        return view('reports.create', [
            'authorities' => ReportingType::where('IS_INTERNET', false)->whereNotNull('AUTHORITY')->distinct()->pluck('AUTHORITY'),
            'cities' => City::orderBy('CITY_NAME')->get(),
            'notifiedAuthorities' => NotifiedAuth::orderBy('Notified_Auth')->pluck('Notified_Auth'),
            'nextRegisterNumber' => $this->buildNextRegisterNumber(),
            'canEditDateTime' => auth()->user()->hasRole('مشرف عام'),
        ]);
    }

    public function reportTypesByAuth(Request $request)
    {
        $auth = $request->query('auth');

        $types = ReportingType::where('AUTHORITY', $auth)
            ->where('IS_INTERNET', false)
            ->orderBy('REPORT_SORT')
            ->get(['REPORT_ID', 'REPORT_SORT']);

        return response()->json($types);
    }

    public function villagesByCity(Request $request, City $city)
    {
        $type = $request->query('type', 'مدينة');

        $query = Village::where('CITY_ID', $city->CITY_ID);

        $query = $type === 'مدينة'
            ? $query->where('VILLAGE_SORT', 'مدينة')
            : $query->where('VILLAGE_SORT', '!=', 'مدينة');

        $villages = $query->orderBy('VILLAGE_NAME')
            ->get(['VILLAGE_ID', 'VILLAGE_NAME', 'VILLAGE_SORT', 'X_AXIS', 'Y_AXIS']);

        return response()->json($villages);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'REPORTER_NAME' => ['required', 'string', 'max:50'],
            'REPORT_FOLLOWUP_NUMBER' => ['required', 'regex:/^(01[0125][0-9]{8}|0[0-9]{9})$/'],
            'REPORTER_SSN' => ['required', 'digits:14', 'regex:/^[23]\d{13}$/'],
            'REPORT_START_DATE' => ['required', 'date'],
            'REPORT_START_TIME' => ['required'],
            'REPORTING_Auth' => ['required', 'string', 'max:500'],
            'REPORTING_SORT' => ['required', 'exists:REPORTING_TYPES,REPORT_ID'],
            'CITY' => ['required', 'exists:CITY,CITY_ID'],
            'location_type' => ['required', 'in:مدينة,قرية'],
            'VILLAGE' => ['required', 'exists:VILLAGE,VILLAGE_ID'],
            'X_AXIS' => ['nullable', 'numeric'],
            'Y_AXIS' => ['nullable', 'numeric'],
            'PLACE_Accident' => ['required', 'string', 'max:500'],
            'DAMAGE' => ['required', 'string'],
            'notified_authorities' => ['nullable', 'array'],
            'notified_authorities.*' => ['string'],
            'injured' => ['nullable', 'array'],
            'injured.*.name' => ['required_with:injured', 'string', 'max:50'],
            'injured.*.birth_date' => ['required_with:injured', 'integer', 'min:0', 'max:150'],
            'injured.*.diagnosis' => ['nullable', 'string', 'max:500'],
            'injured.*.followup' => ['nullable', 'string'],
            'deceased' => ['nullable', 'array'],
            'deceased.*.name' => ['required_with:deceased', 'string', 'max:50'],
            'deceased.*.birth_date' => ['required_with:deceased', 'integer', 'min:0', 'max:150'],
            'deceased.*.address' => ['nullable', 'string'],
            'deceased.*.followup' => ['nullable', 'string'],
        ]);

        $isAdmin = auth()->user()->hasRole('مشرف عام');
        $startDate = $isAdmin ? $validated['REPORT_START_DATE'] : now()->format('Y-m-d');
        $startTime = $isAdmin ? $validated['REPORT_START_TIME'] : now()->format('H:i:s');

        $report = DB::transaction(function () use ($validated, $startDate, $startTime) {
            $report = RecieveReport::create([
                'REPORTER_SSN' => $validated['REPORTER_SSN'],
                'REPORTER_NAME' => $validated['REPORTER_NAME'],
                'REPORTING_Auth' => $validated['REPORTING_Auth'],
                'REPORTING_SORT' => $validated['REPORTING_SORT'],
                'REPORT_START_DATE' => $startDate,
                'REPORT_START_TIME' => $startTime,
                'REPORT_RECIPIENT' => auth()->id(),
                'CITY' => $validated['CITY'],
                'VILLAGE' => $validated['VILLAGE'],
                'X_AXIS' => $validated['X_AXIS'] ?? 0,
                'Y_AXIS' => $validated['Y_AXIS'] ?? 0,
                'DAMAGE' => $validated['DAMAGE'],
                'PLACE_Accident' => $validated['PLACE_Accident'],
                'Deceased_Num' => count($validated['deceased'] ?? []),
                'INFECTED_NUM' => count($validated['injured'] ?? []),
                'REPORT_END_DATE' => null,
                'REPORT_END_TIME' => null,
                'LOCKED_BY' => null,
                'REQUEST_STATUS' => 'تم استلام البلاغ',
                'IS_LOCKED' => false,
                'REPORT_REGISTER_NUMBER' => $this->buildNextRegisterNumber(),
                'REPORT_FOLLOWUP_NUMBER' => $validated['REPORT_FOLLOWUP_NUMBER'],
                'NOTIFIED_AUTHORITIES' => null,
            ]);

            foreach ($validated['notified_authorities'] ?? [] as $authorityName) {
                ReportAuth::create([
                    'REPORT_ID' => $report->ID,
                    'AUTHORITY_ID' => $authorityName,
                ]);
            }

            foreach ($validated['injured'] ?? [] as $injured) {
                Injured::create([
                    'INJURED_NAME' => $injured['name'],
                    'INJURED_AGE' => $injured['birth_date'],
                    'INJURED_DIAGNOSIS' => $injured['diagnosis'] ?? null,
                    'INJURED_FOLLOWUP' => $injured['followup'] ?? null,
                    'REPORT_ID' => $report->ID,
                ]);
            }

            foreach ($validated['deceased'] ?? [] as $deceased) {
                Death::create([
                    'Deceased_NAME' => $deceased['name'],
                    'Deceased_AGE' => $deceased['birth_date'],
                    'Deceased_ADDRESS' => $deceased['address'] ?? null,
                    'Deceased_FOLLOWUP' => $deceased['followup'] ?? null,
                    'REPORT_ID' => $report->ID,
                ]);
            }

            return $report;
        });

        SystemRecord::create([
            'USER_FULL_NAME' => auth()->user()->name,
            'DEVICE_NAME' => substr($request->userAgent() ?? 'غير معروف', 0, 255),
            'MACHINE_IP' => $request->ip(),
            'TITLE' => 'إضافة بلاغ جديد',
            'DESCRIBTION' => 'تمت إضافة بلاغ جديد في النظام بواسطة ' . auth()->user()->name,
            'CREATED_DATE' => now()->format('Y-m-d'),
            'ISACTIVE' => '1',
            'USER_ID' => auth()->id(),
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', "تم تسجيل البلاغ بنجاح برقم {$report->REPORT_REGISTER_NUMBER}");
    }

    protected function buildNextRegisterNumber(): string
    {
        $lastNumber = DB::table('RECIEVE_REPORT')
            ->selectRaw("MAX(CAST(REPLACE(REPORT_REGISTER_NUMBER, 'REP-', '') AS UNSIGNED)) as max_num")
            ->value('max_num');

        return 'REP-' . (((int) $lastNumber) + 1);
    }
}