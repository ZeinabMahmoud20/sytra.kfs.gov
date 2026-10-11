<?php

namespace App\Http\Controllers;

use App\Models\DailyAttendanceEntity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DailyAttendanceEntityController extends Controller
{
    public function markDone(Request $request, DailyAttendanceEntity $dailyAttendanceEntity): RedirectResponse|JsonResponse
    {
        $dailyAttendanceEntity->update([
            'status' => 'done',
            'response_at' => now(),
        ]);

        $this->syncParentStatus($dailyAttendanceEntity);

        if ($request->wantsJson()) {
            return $this->jsonResponse($dailyAttendanceEntity, 'تم تسجيل أداء التمام');
        }

        return back()->with('success', 'تم تسجيل أداء التمام');
    }

    public function markNotDone(Request $request, DailyAttendanceEntity $dailyAttendanceEntity): RedirectResponse|JsonResponse
    {
        $dailyAttendanceEntity->update([
            'status' => 'not_done',
            'response_at' => now(),
        ]);

        $this->syncParentStatus($dailyAttendanceEntity);

        if ($request->wantsJson()) {
            return $this->jsonResponse($dailyAttendanceEntity, 'تم تسجيل عدم أداء التمام');
        }

        return back()->with('success', 'تم تسجيل عدم أداء التمام');
    }

    private function jsonResponse(DailyAttendanceEntity $dailyAttendanceEntity, string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'status' => $dailyAttendanceEntity->status,
            'response_at' => $dailyAttendanceEntity->response_at?->format('h:i A'),
        ]);
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