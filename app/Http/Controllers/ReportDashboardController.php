<?php

namespace App\Http\Controllers;

use App\Exports\AuthorityStatsExport;
use App\Exports\CityPerformanceExport;
use App\Models\City;
use App\Models\RecieveReport;
use App\Models\ReportingType;
use App\Models\Village;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ReportDashboardController extends Controller
{
    public function index(Request $request)
    {
        $from   = $request->input('from');
        $to     = $request->input('to');
        $cityId = $request->input('city');
        $typeId = $request->input('type');
        $status = $request->input('status');
        $villageCenter = $request->input('village_center');

        $baseQuery = function ($query) use ($from, $to) {
            if ($from) $query->where('REPORT_START_DATE', '>=', $from);
            if ($to)   $query->where('REPORT_START_DATE', '<=', $to);
        };

        $query = RecieveReport::query();
        $baseQuery($query);
        if ($cityId) $query->where('CITY', $cityId);
        if ($typeId) $query->where('REPORTING_SORT', $typeId);
        if ($status) $query->where('REQUEST_STATUS', $status);

        $totalReports = (clone $query)->count();

        $todayReports = RecieveReport::whereDate('REPORT_START_DATE', today())->count();

        $weekReports = RecieveReport::whereBetween('REPORT_START_DATE', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek(),
        ])->count();

        $finished = (clone $query)->where('REQUEST_STATUS', 'تم الانتهاء')->count();
        $completionRate = $totalReports > 0 ? round(($finished / $totalReports) * 100, 1) : 0;

        $dailyStats = RecieveReport::whereDate('REPORT_START_DATE', '>=', Carbon::now()->subDays(30))
            ->selectRaw('REPORT_START_DATE as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topCities = City::withCount(['recieveReports as reports_count' => $baseQuery])
            ->having('reports_count', '>', 0)
            ->orderByDesc('reports_count')
            ->limit(10)
            ->get();

        $typeStats = ReportingType::withCount(['recieveReports as count' => $baseQuery])
            ->having('count', '>', 0)
            ->orderByDesc('count')
            ->get();

        $totalDeceased = (clone $query)->sum('Deceased_Num');
        $totalInfected = (clone $query)->sum('INFECTED_NUM');

        $cityPerformance = $this->buildCityPerformance($from, $to);

        $chartDaily = [
            'labels' => $dailyStats->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d/m'))->toArray(),
            'data'   => $dailyStats->pluck('count')->toArray(),
        ];

        $chartCities = [
            'labels' => $topCities->pluck('CITY_NAME')->toArray(),
            'data'   => $topCities->pluck('reports_count')->toArray(),
        ];

        $chartTypes = [
            'labels' => $typeStats->pluck('REPORT_SORT')->toArray(),
            'data'   => $typeStats->pluck('count')->toArray(),
        ];

        $cities = City::orderBy('CITY_NAME')->get();

        // ── التحليل الجغرافي الإحصائي المتعلق بنوع البلاغ ──
        $typesByCity = RecieveReport::query()
            ->when($from, fn($q) => $q->where('REPORT_START_DATE', '>=', $from))
            ->when($to, fn($q) => $q->where('REPORT_START_DATE', '<=', $to))
            ->when($cityId, fn($q) => $q->where('CITY', $cityId))
            ->when($typeId, fn($q) => $q->where('REPORTING_SORT', $typeId))
            ->when($status, fn($q) => $q->where('REQUEST_STATUS', $status))
            ->selectRaw('REPORTING_SORT as type_id, CITY as city_id, COUNT(*) as count')
            ->groupBy('type_id', 'city_id')
            ->orderByDesc('count')
            ->get();

        $cityNamesById  = $cities->keyBy('CITY_ID')->map->CITY_NAME;
        $typeNamesById  = ReportingType::pluck('REPORT_SORT', 'REPORT_ID');

        // ── بناء مصفوفة (مدينة × نوع بلاغ) للرسوم البيانية ──
        $geoMatrix = $typesByCity->mapWithKeys(fn($r) => [
            $r->city_id . '-' . $r->type_id => (int) $r->count,
        ]);

        $placeTotals = $typesByCity->groupBy('city_id')
            ->map(fn($rows) => (int) $rows->sum('count'))
            ->sortDesc();

        $geoTopPlaces = $placeTotals->take(12)->keys()->values();
        $geoTopTypes  = $typesByCity->groupBy('type_id')
            ->map(fn($rows) => (int) $rows->sum('count'))
            ->sortDesc()
            ->keys()
            ->values()
            ->take(8);

        $geoChartCounts   = [];
        $geoChartPercents = [];

        foreach ($geoTopTypes as $typeId) {
            $counts   = [];
            $percents = [];
            foreach ($geoTopPlaces as $cityId) {
                $value = $geoMatrix[$cityId . '-' . $typeId] ?? 0;
                $total = $placeTotals[$cityId] ?? 1;
                $counts[]   = $value;
                $percents[] = $total > 0 ? round(($value / $total) * 100, 1) : 0;
            }
            $geoChartCounts[]   = $counts;
            $geoChartPercents[] = $percents;
        }

        $geoChart = [
            'cities'  => $geoTopPlaces->map(fn($id) => $cityNamesById[$id] ?? 'غير معروف')->all(),
            'types'   => $geoTopTypes->map(fn($id) => $typeNamesById[$id] ?? 'غير معروف')->all(),
            'counts'  => $geoChartCounts,
            'percents'=> $geoChartPercents,
        ];

        $reportTypes = ReportingType::orderBy('REPORT_SORT')->get();

        // ── البلاغات حسب القرية داخل في المركز (مع dropdown) ──
        $villageNamesById = Village::pluck('VILLAGE_NAME', 'VILLAGE_ID');

        $villageQuery = RecieveReport::query()
            ->when($from, fn($q) => $q->where('REPORT_START_DATE', '>=', $from))
            ->when($to, fn($q) => $q->where('REPORT_START_DATE', '<=', $to))
            ->when($status, fn($q) => $q->where('REQUEST_STATUS', $status))
            ->whereNotNull('VILLAGE');

        if ($villageCenter) {
            $villageRows = (clone $villageQuery)
                ->where('CITY', $villageCenter)
                ->selectRaw('VILLAGE, COUNT(*) as count')
                ->groupBy('VILLAGE')
                ->orderByDesc('count')
                ->get();
        } else {
            $villageRows = (clone $villageQuery)
                ->selectRaw('VILLAGE, COUNT(*) as count')
                ->groupBy('VILLAGE')
                ->orderByDesc('count')
                ->limit(15)
                ->get();
        }

        $chartVillages = [
            'center' => $villageCenter
                ? (City::where('CITY_ID', $villageCenter)->value('CITY_NAME') ?? '')
                : 'جميع المراكز',
            'labels' => $villageRows->map(fn($r) => $villageNamesById[$r->VILLAGE] ?? 'غير محدد')->all(),
            'data'   => $villageRows->map(fn($r) => (int) $r->count)->all(),
        ];

        // ── إحصائية حسب جهة البلاغ (REPORTING_Auth): أكثر جهة + أكثر قرية + أكثر نوع بلاغ ──
        $authorityStats = $this->buildAuthorityStats($from, $to, $status);

        $chartAuth = [
            'labels' => $authorityStats->take(7)->pluck('auth')->all(),
            'data'   => $authorityStats->take(7)->pluck('total')->all(),
        ];

        return view('reports.dashboard', compact(
            'totalReports', 'todayReports', 'weekReports', 'completionRate',
            'totalDeceased', 'totalInfected',
            'cityPerformance', 'chartDaily', 'chartCities', 'chartTypes',
            'geoChart',
            'chartVillages', 'authorityStats', 'chartAuth',
            'cities', 'reportTypes',
            'from', 'to', 'cityId', 'typeId', 'status', 'villageCenter',
        ));
    }

    /**
     * بناء أداء المراكز والمدن (مرتبة تنازلياً حسب نسبة الإنجاز).
     */
    protected function buildCityPerformance(?string $from, ?string $to): Collection
    {
        $baseQuery = function ($query) use ($from, $to) {
            if ($from) $query->where('REPORT_START_DATE', '>=', $from);
            if ($to)   $query->where('REPORT_START_DATE', '<=', $to);
        };

        return City::query()
            ->withCount([
                'recieveReports as total_count' => $baseQuery,
                'recieveReports as finished_count' => function ($q) use ($baseQuery) {
                    $q->where('REQUEST_STATUS', 'تم الانتهاء');
                    $baseQuery($q);
                },
                'recieveReports as processing_count' => function ($q) use ($baseQuery) {
                    $q->where('REQUEST_STATUS', 'قيد المعالجة');
                    $baseQuery($q);
                },
                'recieveReports as executed_count' => function ($q) use ($baseQuery) {
                    $q->where('REQUEST_STATUS', 'تم التنفيذ');
                    $baseQuery($q);
                },
                'recieveReports as received_count' => function ($q) use ($baseQuery) {
                    $q->where('REQUEST_STATUS', 'تم استلام البلاغ');
                    $baseQuery($q);
                },
            ])
            ->having('total_count', '>', 0)
            ->get()
            ->map(fn($city) => tap($city, fn($c) => $c->completion_rate = $c->total_count > 0
                ? round(($c->finished_count / $c->total_count) * 100, 1) : 0))
            ->sortByDesc(fn($city) => [$city->completion_rate, $city->finished_count])
            ->values();
    }

    /**
     * بناء إحصائية حسب جهة البلاغ (أكثر الجهات تسجيلاً + أكثر قرية + أكثر نوع بلاغ).
     */
    protected function buildAuthorityStats(?string $from, ?string $to, ?string $status): Collection
    {
        $villageNamesById = Village::pluck('VILLAGE_NAME', 'VILLAGE_ID');
        $typeNamesById    = ReportingType::pluck('REPORT_SORT', 'REPORT_ID');

        $authVillageRows = RecieveReport::query()
            ->when($from, fn($q) => $q->where('REPORT_START_DATE', '>=', $from))
            ->when($to, fn($q) => $q->where('REPORT_START_DATE', '<=', $to))
            ->when($status, fn($q) => $q->where('REQUEST_STATUS', $status))
            ->whereNotNull('REPORTING_Auth')
            ->selectRaw('REPORTING_Auth as auth, VILLAGE, REPORTING_SORT as type_id, COUNT(*) as count')
            ->groupBy('auth', 'VILLAGE', 'type_id')
            ->orderByDesc('count')
            ->get();

        return $authVillageRows
            ->groupBy('auth')
            ->map(function ($rows, $auth) use ($villageNamesById, $typeNamesById) {
                $villages = $rows->groupBy('VILLAGE')
                    ->map(fn($rs) => (int) $rs->sum('count'))
                    ->sortDesc();
                $types = $rows->groupBy('type_id')
                    ->map(fn($rs) => (int) $rs->sum('count'))
                    ->sortDesc();

                return [
                    'auth'          => $auth,
                    'total'         => (int) $rows->sum('count'),
                    'village_count' => $villages->count(),
                    'top_villages'  => $villages->take(3)->map(fn($count, $vid) => [
                        'village' => $villageNamesById[$vid] ?? 'غير محدد',
                        'count'   => $count,
                    ])->values()->all(),
                    'top_types'     => $types->take(3)->map(fn($count, $tid) => [
                        'type'  => $typeNamesById[$tid] ?? 'غير محدد',
                        'count' => $count,
                    ])->values()->all(),
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    /**
     * تصدير أداء المراكز والمدن إلى Excel.
     */
    public function exportCitiesExcel(Request $request)
    {
        $cities = $this->buildCityPerformance($request->input('from'), $request->input('to'));

        return Excel::download(new CityPerformanceExport($cities), 'أداء-المراكز-والمدن.xlsx');
    }

    /**
     * تصدير أداء المراكز والمدن إلى PDF.
     */
    public function exportCitiesPdf(Request $request)
    {
        $cities = $this->buildCityPerformance($request->input('from'), $request->input('to'));

        $arabic = new \ArPHP\I18N\Arabic();

        $rows = $cities->map(fn($city) => [
            'name'            => $arabic->utf8Glyphs($city->CITY_NAME ?? ''),
            'total_count'     => $city->total_count,
            'received_count'  => $city->received_count,
            'processing_count' => $city->processing_count,
            'executed_count'  => $city->executed_count,
            'finished_count'  => $city->finished_count,
            'completion_rate' => $city->completion_rate,
        ]);

        $logicalHeaders = [
            'م',
            'المركز / المدينة',
            'إجمالي البلاغات',
            'تم استلام البلاغ',
            'قيد المعالجة',
            'تم التنفيذ',
            'تم الانتهاء',
            'نسبة الإنجاز',
        ];

        $pdf = Pdf::loadView('reports.dashboard-cities-pdf', [
            'rows'          => $rows,
            'pageTitle'     => $arabic->utf8Glyphs('أداء المراكز والمدن - الشبكة الوطنية للطوارئ بكفر الشيخ'),
            'printedAtLabel' => $arabic->utf8Glyphs('تاريخ الطباعة:'),
            'totalLabel'    => $arabic->utf8Glyphs('عدد المراكز:'),
            'tableHeaders'  => array_map(fn($h) => $arabic->utf8Glyphs($h), array_reverse($logicalHeaders)),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('أداء-المراكز-والمدن.pdf');
    }

    /**
     * تصدير إحصائية جهات البلاغ إلى Excel.
     */
    public function exportAuthoritiesExcel(Request $request)
    {
        $authorities = $this->buildAuthorityStats($request->input('from'), $request->input('to'), $request->input('status'));

        return Excel::download(new AuthorityStatsExport($authorities), 'إحصائية-جهات-البلاغ.xlsx');
    }

    /**
     * تصدير إحصائية جهات البلاغ إلى PDF.
     */
    public function exportAuthoritiesPdf(Request $request)
    {
        $authorities = $this->buildAuthorityStats($request->input('from'), $request->input('to'), $request->input('status'));

        $arabic = new \ArPHP\I18N\Arabic();

        $rows = $authorities->map(fn($item) => [
            'auth'          => $arabic->utf8Glyphs($item['auth']),
            'total'         => $item['total'],
            'village_count' => $item['village_count'],
            'top_villages'  => $arabic->utf8Glyphs(collect($item['top_villages'])
                ->map(fn($v) => $v['village'] . ' (' . $v['count'] . ')')->implode('، ')),
            'top_types'     => $arabic->utf8Glyphs(collect($item['top_types'])
                ->map(fn($t) => $t['type'] . ' (' . $t['count'] . ')')->implode('، ')),
        ]);

        $logicalHeaders = [
            'م',
            'جهة البلاغ',
            'عدد البلاغات',
            'عدد القرى',
            'أكثر قرية',
            'أكثر نوع بلاغ',
        ];

        $pdf = Pdf::loadView('reports.dashboard-authorities-pdf', [
            'rows'          => $rows,
            'pageTitle'     => $arabic->utf8Glyphs('إحصائية حسب جهة البلاغ - الشبكة الوطنية للطوارئ بكفر الشيخ'),
            'printedAtLabel' => $arabic->utf8Glyphs('تاريخ الطباعة:'),
            'totalLabel'    => $arabic->utf8Glyphs('عدد الجهات:'),
            'tableHeaders'  => array_map(fn($h) => $arabic->utf8Glyphs($h), array_reverse($logicalHeaders)),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('إحصائية-جهات-البلاغ.pdf');
    }
}
