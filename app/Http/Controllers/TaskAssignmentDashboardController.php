<?php

namespace App\Http\Controllers;

use App\Models\TaskAssignment;
use App\Models\TaskEntity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * لوحة متابعة التكليفات - مؤشرات التنفيذ والزمن والجهات بين تاريخين.
 *
 * كل مستخدم يشوف نطاقه فقط حسب دوره (visibleTasksQuery في User).
 */
class TaskAssignmentDashboardController extends Controller
{
    private const STATUSES = ['تم التنفيذ', 'جاري التنفيذ', 'متأخر', 'لم يبدأ', 'متوقف'];

    /** تطبيق فلتر التاريخ (created_at) + نطاق رؤية المستخدم على أي استعلام */
    private function baseQuery(Request $request)
    {
        $query = Auth::user()->visibleTasksQuery();

        // فلتر التاريخ بين تاريخين على created_at (تاريخ الإنشاء)
        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }

    public function index(Request $request)
    {
        // ---------- مؤشرات التنفيذ ----------
        $statusCounts = (clone $this->baseQuery($request))
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalTasks = $statusCounts->sum();

        $executionIndicators = [
            'total'         => $totalTasks,
            'completed'     => $statusCounts['تم التنفيذ'] ?? 0,
            'in_progress'   => $statusCounts['جاري التنفيذ'] ?? 0,
            'overdue'       => $statusCounts['متأخر'] ?? 0,
            'not_started'   => $statusCounts['لم يبدأ'] ?? 0,
            'halted'        => $statusCounts['متوقف'] ?? 0,
        ];

        // ---------- مؤشرات الزمن ----------
        // متوسط زمن الاستجابة (أيام): received_date -> response_date
        $avgResponse = (clone $this->baseQuery($request))
            ->whereNotNull('received_date')
            ->whereNotNull('response_date')
            ->get(['received_date', 'response_date'])
            ->avg(fn ($t) => max(0, $t->response_date->diffInDays($t->received_date)));

        // متوسط زمن التنفيذ الفعلي (أيام تأخير): استخدم delay_days
        $avgDelay = (clone $this->baseQuery($request))
            ->get()
            ->avg(fn ($t) => $t->delay_days);

        // نسبة الالتزام بالمواعيد: المنفذ في الوقت / إجمالي التكليفات
        $completed = (clone $this->baseQuery($request))
            ->where('status', 'تم التنفيذ')
            ->whereNotNull('deadline')
            ->whereNotNull('response_date')
            ->get(['deadline', 'response_date'])
            ->where(fn ($t) => ! $t->response_date->gt($t->deadline))
            ->count();

        $commitmentRate = $totalTasks > 0 ? round(($completed / $totalTasks) * 100, 1) : 0;

        // متوسط نسبة الإنجاز العامة (متوسط completion_percentage لكل التكليفات)
        $avgCompletion = (clone $this->baseQuery($request))
            ->where('status', '!=', 'لم يبدأ')
            ->avg('completion_percentage') ?? 0;
        $avgCompletion = round($avgCompletion, 1);

        // ---------- مؤشرات الجهات ----------
        $entities = TaskEntity::active()
            ->withCount([
                'tasks as total_tasks' => function ($q) use ($request) {
                    $this->applyDateFilterToQuery($q, $request);
                },
                'tasks as completed_tasks' => function ($q) use ($request) {
                    $this->applyDateFilterToQuery($q, $request);
                    $q->where('status', 'تم التنفيذ');
                },
                'tasks as in_progress_tasks' => function ($q) use ($request) {
                    $this->applyDateFilterToQuery($q, $request);
                    $q->where('status', 'جاري التنفيذ');
                },
                'tasks as overdue_tasks' => function ($q) use ($request) {
                    $this->applyDateFilterToQuery($q, $request);
                    $q->where('status', 'متأخر');
                },
                'tasks as not_started_tasks' => function ($q) use ($request) {
                    $this->applyDateFilterToQuery($q, $request);
                    $q->where('status', 'لم يبدأ');
                },
                'tasks as halted_tasks' => function ($q) use ($request) {
                    $this->applyDateFilterToQuery($q, $request);
                    $q->where('status', 'متوقف');
                },
            ])
            ->having('total_tasks', '>', 0)
            ->orderByDesc('total_tasks')
            ->get()
            ->map(function ($entity) {
                $entity->completion_rate = $entity->total_tasks > 0
                    ? round(($entity->completed_tasks / $entity->total_tasks) * 100, 1)
                    : 0;
                return $entity;
            });

        return view('tasks.dashboard', [
            'executionIndicators' => $executionIndicators,
            'avgResponse'   => round((float) $avgResponse, 1),
            'avgDelay'      => round((float) $avgDelay, 1),
            'commitmentRate'=> $commitmentRate,
            'avgCompletion' => $avgCompletion,
            'entities'      => $entities,
            'chartEntities' => $entities
                ->map(fn ($e) => [
                    'name'        => $e->name,
                    'completed'   => $e->completed_tasks,
                    'in_progress' => $e->in_progress_tasks,
                    'overdue'     => $e->overdue_tasks,
                ])
                ->values(),
            'statuses'      => self::STATUSES,
            'from'          => $request->input('from'),
            'to'            => $request->input('to'),
        ]);
    }

    /** تطبيق فلتر التاريخ (created_at) على استعلام علاقة */
    private function applyDateFilterToQuery($query, Request $request): void
    {
        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }
    }
}
