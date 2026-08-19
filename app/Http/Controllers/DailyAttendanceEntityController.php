<?php

namespace App\Http\Controllers;

use App\Models\DailyAttendanceEntity;
use Illuminate\Http\RedirectResponse;

class DailyAttendanceEntityController extends Controller
{
    public function markDone(DailyAttendanceEntity $dailyAttendanceEntity): RedirectResponse
    {
        $dailyAttendanceEntity->update([
            'status' => 'done',
            'response_at' => now(),
        ]);

        $this->syncParentStatus($dailyAttendanceEntity);

        return back()->with('success', 'تم تسجيل أداء التمام');
    }

    public function markNotDone(DailyAttendanceEntity $dailyAttendanceEntity): RedirectResponse
    {
        $dailyAttendanceEntity->update([
            'status' => 'not_done',
            'response_at' => now(),
        ]);

        $this->syncParentStatus($dailyAttendanceEntity);

        return back()->with('success', 'تم تسجيل عدم أداء التمام');
    }

    /**
     * تحديث حالة الـ DailyAttendance الأب بناءً على حالة الجهات المرتبطة به.
     */
    private function syncParentStatus(DailyAttendanceEntity $dailyAttendanceEntity): void
    {
        $dailyAttendance = $dailyAttendanceEntity->dailyAttendance;

        $entities = $dailyAttendance->dailyAttendanceEntities;

        $hasPending = $entities->contains('status', 'pending');
        $allResponded = ! $hasPending;

        if ($dailyAttendance->status === 'created') {
            $dailyAttendance->update([
                'status' => 'in_progress',
                'started_at' => $dailyAttendance->started_at ?? now(),
            ]);
        }

        if ($allResponded && $dailyAttendance->status !== 'completed') {
            $dailyAttendance->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }
    }
}