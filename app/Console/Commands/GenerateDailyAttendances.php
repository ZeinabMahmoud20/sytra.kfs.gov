<?php

namespace App\Console\Commands;

use App\Models\AttendanceTemplate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateDailyAttendances extends Command
{
    protected $signature = 'attendances:generate-daily';

    protected $description = 'إنشاء سجلات التمامات اليومية واختيار الجهات لكل تمام نشط';

    public function handle(): int
    {
        $today = now()->toDateString();

        $templates = AttendanceTemplate::where('is_active', true)->get();

        foreach ($templates as $template) {

            // لو السجل موجود بالفعل لليوم ده، تجاهل التمام ده
            $alreadyExists = $template->dailyAttendances()
                ->whereDate('attendance_date', $today)
                ->exists();

            if ($alreadyExists) {
                $this->line("Skipped: {$template->name} (already generated for {$today})");
                continue;
            }

            DB::transaction(function () use ($template, $today) {

                // جيب الـ Cycle الحالية، أو أنشئ أول واحدة لو مفيش
                $cycle = $template->currentCycle();

                if (! $cycle) {
                    $cycle = $template->cycles()->create(['cycle_number' => 1]);
                }

                $allEntityIds = $template->entities()->pluck('entities.id')->toArray();
                $usedEntityIds = $cycle->usedEntityIds();
                $remainingEntityIds = array_values(array_diff($allEntityIds, $usedEntityIds));

                // لو خلصت كل الجهات في الـ Cycle الحالية، ابدأ Cycle جديدة
                if (empty($remainingEntityIds)) {
                    $cycle = $template->cycles()->create([
                        'cycle_number' => $cycle->cycle_number + 1,
                    ]);

                    $remainingEntityIds = $allEntityIds;
                }

                $countToPick = min($template->daily_entities_count, count($remainingEntityIds));

                $selectedEntityIds = collect($remainingEntityIds)
                    ->shuffle()
                    ->take($countToPick)
                    ->values()
                    ->toArray();

                $dailyAttendance = $template->dailyAttendances()->create([
                    'attendance_cycle_id' => $cycle->id,
                    'attendance_date' => $today,
                    'status' => 'created',
                ]);

                foreach ($selectedEntityIds as $entityId) {
                    $dailyAttendance->dailyAttendanceEntities()->create([
                        'entity_id' => $entityId,
                        'status' => 'pending',
                    ]);
                }
            });

            $this->info("Generated: {$template->name} for {$today}");
        }

        return self::SUCCESS;
    }
}