<?php

namespace App\Http\Controllers;

use App\Models\DailyAttendance;
use Illuminate\View\View;

class DailyAttendanceController extends Controller
{
    public function index(): View
    {
        $dailyAttendances = DailyAttendance::with(['attendanceTemplate', 'dailyAttendanceEntities'])
            ->whereDate('attendance_date', today())
            ->orderBy('created_at')
            ->get();

        return view('daily-attendances.index', compact('dailyAttendances'));
    }

    public function show(DailyAttendance $dailyAttendance): View
    {
        $dailyAttendance->load(['attendanceTemplate', 'dailyAttendanceEntities.entity']);

        return view('daily-attendances.show', compact('dailyAttendance'));
    }

    public function pendingReminders(): \Illuminate\Http\JsonResponse
{
    $now = now();

    $dueAttendances = DailyAttendance::with('attendanceTemplate')
        ->whereDate('attendance_date', $now->toDateString())
        ->where('status', '!=', 'completed')
        ->get()
        ->filter(function ($daily) use ($now) {
            $attendanceDateTime = $daily->attendance_date->copy()->setTimeFromTimeString(
                $daily->attendanceTemplate->attendance_time->format('H:i:s')
            );

            return $now->greaterThanOrEqualTo($attendanceDateTime);
        })
        ->map(function ($daily) {
            return [
                'id' => $daily->id,
                'template_name' => $daily->attendanceTemplate->name,
                'time' => $daily->attendanceTemplate->attendance_time->format('h:i A'),
                'url' => route('daily-attendances.show', $daily->id),
            ];
        })
        ->values();

    return response()->json($dueAttendances);
}

}