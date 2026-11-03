<?php

namespace App\Http\Controllers;

use App\Models\DailyAttendance;
use App\Models\RecieveReport;
use App\Models\RecieveSignal;
use App\Models\SignalUnit;
use App\Models\TaskAssignment;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{

    public function index()
    {
        // ملحوظة: استخدمنا Schema::hasTable() كحماية مؤقتة، عشان لو حد
        // شغّل الصفحة قبل ما يعمل migrate للجداول دي، الصفحة تفضل شغالة
        // من غير Error بدل ما توقف المشروع كله. لما تتأكد كل الجداول
        // موجودة، تقدر تشيل الشرط ده وتسيب الكويري مباشرة.

        $totalReports = Schema::hasTable('RECIEVE_REPORT')
            ? RecieveReport::count()
            : 0;

        $activeIncidents = Schema::hasTable('RECIEVE_REPORT')
            ? RecieveReport::where('REQUEST_STATUS', '!=', 'تم التنفيذ')->count()
            : 0;

        $todaySignals = Schema::hasTable('SIGNAL_UNIT')
            ? SignalUnit::whereDate('UNIT_SIGNAL_DATE', today())->count()
            : 0;

        $totalSignals = Schema::hasTable('SIGNAL_UNIT')
            ? SignalUnit::count()
            : 0;

        // إجمالي التمامات (كل التمامات المسجلة) وتمامات اليوم
        $totalTmams = Schema::hasTable('daily_attendances')
            ? DailyAttendance::count()
            : 0;

        $todayTmams = Schema::hasTable('daily_attendances')
            ? DailyAttendance::whereDate('attendance_date', today())->count()
            : 0;

        // إجمالي التكليفات والتكليفات الجارية (غير المنتهية)
        $totalTasks = Schema::hasTable('task_assignments')
            ? TaskAssignment::count()
            : 0;

        $activeTasks = Schema::hasTable('task_assignments')
            ? TaskAssignment::where('status', '!=', 'تم التنفيذ')->count()
            : 0;

        // نسبة تممات منفذة - قيمة مبدئية لحد ما نبني موديول التمام فعلياً
        $tmamCompletionRate = 0;

        $recentReports = Schema::hasTable('RECIEVE_REPORT')
            ? RecieveReport::with('reportingType')
                ->latest('ID')
                ->take(5)
                ->get()
            : collect();

        return view('dashboard', [
            'totalReports' => $totalReports,
            'activeIncidents' => $activeIncidents,
            'todaySignals' => $todaySignals,
            'totalSignals' => $totalSignals,
            'totalTmams' => $totalTmams,
            'todayTmams' => $todayTmams,
            'totalTasks' => $totalTasks,
            'activeTasks' => $activeTasks,
            'tmamCompletionRate' => $tmamCompletionRate,
            'recentReports' => $recentReports,
        ]);
    }
}