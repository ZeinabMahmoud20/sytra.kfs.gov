<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\RecieveReport;
use Illuminate\Http\Request;

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

    public function index(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        $monthName = self::MONTHS[$month] ?? '';

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
            ->sortByDesc(fn ($city) => [$city->completion_rate, $city->finished_count, $city->total_count]);

        $totals = [
            'total'      => $rowStats->sum('total_count'),
            'received'   => $rowStats->sum('received_count'),
            'processing' => $rowStats->sum('processing_count'),
            'executed'   => $rowStats->sum('executed_count'),
            'finished'   => $rowStats->sum('finished_count'),
        ];

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
}