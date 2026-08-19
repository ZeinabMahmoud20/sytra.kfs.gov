<?php

namespace App\Console\Commands;

use App\Models\Evaluation;
use App\Models\EvaluationEntity;
use App\Models\MonthlyEvaluation;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CalculateMonthlyEvaluations extends Command
{
    /**
     * php artisan evaluations:calculate-monthly
     * php artisan evaluations:calculate-monthly --year=2026 --month=7
     */
    protected $signature = 'evaluations:calculate-monthly {--year=} {--month=}';

    protected $description = 'حساب النسبة والدرجة الشهرية لكل جهة، وترتيبها، وتخزين النتيجة في monthly_evaluations';

    public function handle(): int
    {
        // افتراضيًا بيحسب الشهر اللي فات (يعني لو اتشغل أول يوم في الشهر الجديد بيحسب الشهر اللي قبله)
        $target = Carbon::now()->subMonthNoOverflow();
        $year   = (int) ($this->option('year') ?: $target->year);
        $month  = (int) ($this->option('month') ?: $target->month);

        $this->info("بحسب نتيجة شهر {$month}/{$year} ...");

        $workingDaysCount = $this->countWorkingDays($year, $month);
        $maxPossibleScore = $workingDaysCount * 3;

        if ($maxPossibleScore === 0) {
            $this->warn('مفيش أيام عمل في الشهر ده (تحقق من التاريخ).');
            return self::FAILURE;
        }

        $entities = EvaluationEntity::query()->get();

        DB::transaction(function () use ($entities, $year, $month, $maxPossibleScore) {
            $results = [];

            foreach ($entities as $entity) {
                $totalScore = Evaluation::query()
                    ->where('evaluation_entity_id', $entity->id)
                    ->whereYear('evaluation_date', $year)
                    ->whereMonth('evaluation_date', $month)
                    ->sum('score');

                $percentage = round(($totalScore / $maxPossibleScore) * 100, 2);
                $grade20    = round($percentage * 0.2, 2); // الدرجة من 20 = النسبة% × 0.2

                $results[] = [
                    'entity_id'   => $entity->id,
                    'total_score' => $totalScore,
                    'percentage'  => $percentage,
                    'grade_20'    => $grade20,
                ];
            }

            // ترتيب الجهات من الأعلى نسبة للأقل عشان نحدد الـ rank
            usort($results, fn ($a, $b) => $b['percentage'] <=> $a['percentage']);

            foreach ($results as $index => $row) {
                MonthlyEvaluation::updateOrCreate(
                    [
                        'evaluation_entity_id' => $row['entity_id'],
                        'year'  => $year,
                        'month' => $month,
                    ],
                    [
                        'total_score'        => $row['total_score'],
                        'max_possible_score' => $maxPossibleScore, // TODO: عدّلها لو محتاجة max مختلف لكل جهة (جهة اتضافت نص الشهر مثلاً)
                        'percentage'         => $row['percentage'],
                        'grade_out_of_20'    => $row['grade_20'],
                        'rank'               => $index + 1,
                    ]
                );
            }
        });

        $this->info('تم حساب النتائج بنجاح.');

        return self::SUCCESS;
    }

    /**
     * عدد أيام العمل في الشهر (كل الأيام ماعدا الجمعة)
     */
    private function countWorkingDays(int $year, int $month): int
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = Carbon::create($year, $month, 1)->endOfMonth();

        $period = CarbonPeriod::create($start, $end);

        $count = 0;
        foreach ($period as $day) {
            if (! $day->isFriday()) {
                $count++;
            }
        }

        return $count;
    }
}
