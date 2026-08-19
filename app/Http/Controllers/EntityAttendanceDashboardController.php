<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Models\DailyAttendanceEntity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class EntityAttendanceDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->input('date_from'))->startOfDay()
            : Carbon::now()->subDays(29)->startOfDay();

        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->input('date_to'))->endOfDay()
            : Carbon::now()->endOfDay();

        $allEntities = Entity::orderBy('name')->get();

        $entityStats = $allEntities->map(function (Entity $entity) use ($dateFrom, $dateTo) {
            $query = DailyAttendanceEntity::where('entity_id', $entity->id)
                ->whereHas('dailyAttendance', function ($q) use ($dateFrom, $dateTo) {
                    $q->whereBetween('attendance_date', [$dateFrom->toDateString(), $dateTo->toDateString()]);
                });

            $total = $query->count();
            $done = (clone $query)->where('status', 'done')->count();
            $notDone = (clone $query)->where('status', 'not_done')->count();

            return [
                'entity' => $entity,
                'total' => $total,
                'done' => $done,
                'not_done' => $notDone,
            ];
        });

        $totalAppearances = $entityStats->sum('total');
        $totalDone = $entityStats->sum('done');
        $totalNotDone = $entityStats->sum('not_done');
        $doneRate = $totalAppearances > 0
            ? round(($totalDone / $totalAppearances) * 100, 1)
            : 0;

        $top5Done = $entityStats->filter(fn ($s) => $s['done'] > 0)
            ->sortByDesc('done')->take(5)->values();
        $top5NotDone = $entityStats->filter(fn ($s) => $s['not_done'] > 0)
            ->sortByDesc('not_done')->take(5)->values();

        $page = $request->input('page', 1);
        $perPage = 10;
        $paginatedStats = new LengthAwarePaginator(
            $entityStats->slice(($page - 1) * $perPage, $perPage),
            $entityStats->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('entity-attendance.index', [
            'entityStats' => $paginatedStats,
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
            'totalAppearances' => $totalAppearances,
            'totalDone' => $totalDone,
            'totalNotDone' => $totalNotDone,
            'doneRate' => $doneRate,
            'top5Done' => $top5Done,
            'top5NotDone' => $top5NotDone,
        ]);
    }
}
