<?php

namespace App\Http\Controllers;

use App\Exports\CityStatsExport;
use App\Models\City;
use App\Models\RecieveReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * إحصائيات شهرية للبلاغات حسب المركز/المدينة مع رسم بياني.
 *
 * الجدول معروض تنازلياً حسب نسبة التنفيذ (نسبة البلاغات "تم الانتهاء" من الإجمالي).
 */
class MonthlyReportStatsController extends Controller
{
    protected const MONTHS = [
        1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل', 5 => 'مايو', 6 => 'يونيو',
        7 => 'يوليو', 8 => 'أغسطس', 9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
    ];

    protected const STATUSES = [
        'executed'  => 'تم التنفيذ',
        'processing' => 'قيد المعالجة',
        'finished'  => 'تم الانتهاء',
        'received'  => 'تم استلام البلاغ',
    ];

    /**
     * بناء نتيجة الإحصائيات (صفوف المراكز/المدن + الإجماليات) للشهر والسنة.
     *
     * @return array{0: \Illuminate\Support\Collection, 1: array}
     */
    protected function buildStats(int $month, int $year): array
    {
        $applyFilter = function ($query) use ($month, $year) {
            $query->whereYear('REPORT_START_DATE', $year)
                ->whereMonth('REPORT_START_DATE', $month);
        };

        $rowStats = City::query()
            ->withCount([
                'recieveReports as total_count'          => $applyFilter,
                'recieveReports as received_count'       => function ($q) use ($applyFilter) {
                    $applyFilter($q);
                    $q->where('REQUEST_STATUS', self::STATUSES['received']);
                },
                'recieveReports as processing_count'     => function ($q) use ($applyFilter) {
                    $applyFilter($q);
                    $q->where('REQUEST_STATUS', self::STATUSES['processing']);
                },
                'recieveReports as executed_count'       => function ($q) use ($applyFilter) {
                    $applyFilter($q);
                    $q->where('REQUEST_STATUS', self::STATUSES['executed']);
                },
                'recieveReports as finished_count'       => function ($q) use ($applyFilter) {
                    $applyFilter($q);
                    $q->where('REQUEST_STATUS', self::STATUSES['finished']);
                },
            ])
            ->having('total_count', '>', 0)
            ->orderByDesc('total_count')
            ->get()
            ->map(fn ($city) => tap($city, fn ($c) => $c->completion_rate = $c->total_count > 0
                ? round(($c->finished_count / $c->total_count) * 100, 1)
                : 0))
            ->sortByDesc(fn ($city) => [$city->completion_rate, $city->finished_count, $city->total_count])
            ->values();

        $totals = [
            'total'      => $rowStats->sum('total_count'),
            'received'   => $rowStats->sum('received_count'),
            'processing' => $rowStats->sum('processing_count'),
            'executed'   => $rowStats->sum('executed_count'),
            'finished'   => $rowStats->sum('finished_count'),
        ];

        return [$rowStats, $totals];
    }

    public function index(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        $monthName = self::MONTHS[$month] ?? '';

        [$rowStats, $totals] = $this->buildStats($month, $year);

        $years = RecieveReport::whereNotNull('REPORT_START_DATE')
            ->selectRaw('YEAR(REPORT_START_DATE) as y')
            ->distinct()
            ->orderByDesc('y')
            ->pluck('y')
            ->map(fn ($v) => (int) $v);

        if ($years->doesntContain(now()->year)) {
            $years->push(now()->year);
        }

        return view('reports.monthly-stats', [
            'month'      => $month,
            'year'       => $year,
            'monthName'  => $monthName,
            'months'     => self::MONTHS,
            'years'      => $years->sortDesc()->values(),
            'rowStats'   => $rowStats,
            'totals'     => $totals,
            'chartCities' => $rowStats->map(fn ($c) => [
                'name'       => $c->CITY_NAME,
                'received'   => $c->received_count,
                'processing' => $c->processing_count,
                'executed'   => $c->executed_count,
                'finished'   => $c->finished_count,
            ])->values(),
            'statuses'   => array_values(self::STATUSES),
            'chartTotals' => $totals,
        ]);
    }

    /**
     * تصدير إحصائيات المراكز والمدن إلى ملف Excel.
     */
    public function exportExcel(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        [$rowStats, $totals] = $this->buildStats($month, $year);

        $fileName = 'إحصائيات-المراكز-' . (self::MONTHS[$month] ?? '') . '-' . $year . '.xlsx';

        return Excel::download(new CityStatsExport($rowStats, $totals, $month, $year), $fileName);
    }

    /**
     * تصدير إحصائيات المراكز والمدن إلى ملف PDF.
     */
    public function exportPdf(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        [$rowStats, $totals] = $this->buildStats($month, $year);

        // معالجة النصوص العربية (وصل الحروف ببعض) قبل ما تدخل الـ PDF
        $arabic = new \ArPHP\I18N\Arabic();

        $rows = $rowStats->map(fn ($city) => [
            'name'            => $arabic->utf8Glyphs($city->CITY_NAME ?? ''),
            'total_count'     => $city->total_count,
            'received_count'  => $city->received_count,
            'processing_count' => $city->processing_count,
            'executed_count'  => $city->executed_count,
            'finished_count'  => $city->finished_count,
            'completion_rate' => $city->completion_rate,
        ]);

        // الـ dompdf مش بيقبل RTL، فبنعكس ترتيب الأعمدة عشان أول عمود يظهر يمين.
        $logicalHeaders = [
            'م',
            'المركز / المدينة',
            'إجمالي البلاغات',
            'تم استلام البلاغ',
            'قيد المعالجة',
            'تم التنفيذ',
            'تم الانتهاء',
            'نسبة التنفيذ',
        ];

        $pdf = Pdf::loadView('reports.monthly-stats-pdf', [
            'rows'          => $rows,
            'totals'        => $totals,
            'monthName'     => $arabic->utf8Glyphs(self::MONTHS[$month] ?? ''),
            'year'          => $year,
            'pageTitle'     => $arabic->utf8Glyphs('إحصائيات المراكز والمدن - الشبكة الوطنية للطوارئ بكفر الشيخ'),
            'summaryTitle'  => $arabic->utf8Glyphs('ملخص الأداء'),
            'printedAtLabel' => $arabic->utf8Glyphs('تاريخ الطباعة:'),
            'totalLabel'    => $arabic->utf8Glyphs('إجمالي البلاغات:'),
            'totalRowLabel' => $arabic->utf8Glyphs('الإجمالي'),
            'tableHeaders'  => array_map(fn ($header) => $arabic->utf8Glyphs($header), array_reverse($logicalHeaders)),
        ])->setPaper('a4', 'landscape');

        $fileName = 'إحصائيات-المراكز-' . (self::MONTHS[$month] ?? '') . '-' . $year . '.pdf';

        return $pdf->download($fileName);
    }
}