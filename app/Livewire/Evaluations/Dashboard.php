<?php

namespace App\Livewire\Evaluations;

use App\Console\Commands\CalculateMonthlyEvaluations;
use App\Models\MonthlyEvaluation;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public int $year;
    public int $month;

    public function mount(): void
    {
        $this->authorize('evaluations.dashboard');
        $this->year  = (int) now()->year;
        $this->month = (int) now()->subMonthNoOverflow()->month; // افتراضيًا آخر شهر مكتمل
    }

    public function recalculate(): void
    {
        $this->authorize('evaluations.manage'); // أو صلاحية أدق زي recalculate-evaluations

        Artisan::call(CalculateMonthlyEvaluations::class, [
            '--year'  => $this->year,
            '--month' => $this->month,
        ]);

        session()->flash('success', 'تم إعادة حساب نتيجة الشهر');
    }

    public function render()
    {
        $results = MonthlyEvaluation::with('entity')
            ->forMonth($this->year, $this->month)
            ->orderBy('rank')
            ->get();

        return view('livewire.evaluations.dashboard', [
            'results' => $results,
            'top'     => $results->first(),
            'bottom'  => $results->last(),
        ]);
    }
}
