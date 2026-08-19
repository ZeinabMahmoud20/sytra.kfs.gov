<?php

use App\Console\Commands\CalculateMonthlyEvaluations;
use App\Console\Commands\GenerateDailyAttendances;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
Schedule::command(GenerateDailyAttendances::class)
    ->dailyAt('11:27');
Schedule::command(CalculateMonthlyEvaluations::class, [
    '--year'  => now()->year,
    '--month' => now()->month,
])
    ->daily()
    ->at('23:55')
    ->timezone('Africa/Cairo')
    ->withoutOverlapping();