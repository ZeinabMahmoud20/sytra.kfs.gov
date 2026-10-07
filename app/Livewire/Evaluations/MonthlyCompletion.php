<?php

namespace App\Livewire\Evaluations;

use App\Models\Evaluation;
use App\Models\EvaluationEntity;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class MonthlyCompletion extends Component
{
    public function mount(): void
    {
        $user = auth()->user();

        abort_unless(
            $user && (
                $user->can('evaluations.evaluate')
                || $user->can('evaluations.manage')
                || $user->can('evaluations.dashboard')
            ),
            403
        );
    }

    public function render()
    {
        $now       = now();
        $year      = (int) $now->year;
        $month     = (int) $now->month;
        $today     = (int) $now->day;
        $daysInMonth = (int) $now->daysInMonth;

        $entities = EvaluationEntity::active()->orderBy('name')->get();
        $expected = $entities->count();

        $ratedByDay = Evaluation::query()
            ->whereYear('evaluation_date', $year)
            ->whereMonth('evaluation_date', $month)
            ->get(['evaluation_date', 'evaluation_entity_id'])
            ->groupBy(fn (Evaluation $evaluation) => (int) $evaluation->evaluation_date->day)
            ->map(fn ($rows) => $rows->pluck('evaluation_entity_id')->unique()->values());

        $dayNames = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
        $monthNames = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل', 5 => 'مايو', 6 => 'يونيو',
            7 => 'يوليو', 8 => 'أغسطس', 9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ];

        $days           = [];
        $incomplete     = [];
        $ratedTotal     = 0;
        $expectedTotal  = 0;
        $completeDays   = 0;
        $workingDays    = 0;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date     = Carbon::create($year, $month, $day, 0, 0, 0);
            $isFriday = $date->isFriday();
            $isFuture = $day > $today;
            $isToday  = $day === $today;

            $ratedIds = $ratedByDay->get($day, collect());

            $missing = ($isFriday || $isFuture || $expected === 0)
                ? collect()
                : $entities->reject(fn ($entity) => $ratedIds->contains($entity->id));

            $rated      = $expected - $missing->count();
            $percentage = $expected > 0 ? (int) round(($rated / $expected) * 100) : 0;

            if (!$isFriday && !$isFuture) {
                $workingDays++;
                $expectedTotal += $expected;
                $ratedTotal    += $rated;

                if ($missing->isEmpty()) {
                    $completeDays++;
                } else {
                    $incomplete[] = [
                        'day'      => $day,
                        'day_name' => $dayNames[$date->dayOfWeek],
                        'is_today' => $isToday,
                        'rated'    => $rated,
                        'percentage'   => $percentage,
                        'missing'      => $missing->pluck('name')->all(),
                    ];
                }
            }

            $days[] = [
                'day'          => $day,
                'day_name'     => $dayNames[$date->dayOfWeek],
                'is_friday'    => $isFriday,
                'is_future'    => $isFuture,
                'is_today'     => $isToday,
                'status'       => $isFriday ? 'friday'
                    : ($isFuture ? 'future'
                        : ($percentage === 100 ? 'complete'
                            : ($percentage > 0 ? 'partial' : 'missing'))),
                'percentage'   => $percentage,
                'rated'        => $rated,
                'expected'     => $expected,
                'missing'      => $missing->pluck('name')->all(),
            ];
        }

        return view('livewire.evaluations.monthly-completion', [
            'monthName'        => $monthNames[$month],
            'year'             => $year,
            'today'            => $today,
            'daysInMonth'      => $daysInMonth,
            'days'             => $days,
            'entitiesCount'    => $expected,
            'incomplete'       => $incomplete,
            'completeDays'     => $completeDays,
            'workingDays'      => $workingDays,
            'ratedTotal'       => $ratedTotal,
            'expectedTotal'    => $expectedTotal,
            'monthPercentage'  => $expectedTotal > 0 ? round(($ratedTotal / $expectedTotal) * 100, 1) : null,
        ]);
    }
}
