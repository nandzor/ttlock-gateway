<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\AggregateApiUsageJob;
use App\Jobs\AggregateWhatsAppDeliveryJob;
use App\Jobs\UpdateDailyReportJob;
use App\Jobs\UpdateMonthlyReportJob;
use App\Jobs\CleanupOldFilesJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Scheduled Tasks
Schedule::call(function () {
    $yesterday = now()->yesterday()->toDateString();
    AggregateApiUsageJob::dispatch($yesterday)->onQueue('reports');
    AggregateWhatsAppDeliveryJob::dispatch($yesterday)->onQueue('reports');
})->dailyAt('01:30')->name('aggregate_daily_logs')->withoutOverlapping();

Schedule::call(function () {
    $yesterday = now()->yesterday()->toDateString();
    UpdateDailyReportJob::dispatch($yesterday)->onQueue('reports');
})->dailyAt('01:00')->name('update_daily_reports')->withoutOverlapping();

// Generate counting reports from detections (for daily/monthly reports)
Schedule::command('reports:generate-counting --days=1')
    ->dailyAt('01:15')
    ->name('generate_counting_reports')
    ->withoutOverlapping();

// Update daily reports every 5 minutes (moved from ProcessDetectionJob)
Schedule::call(function () {
    $today = now()->toDateString();
    $branches = \App\Models\CompanyBranch::where('status', 'active')->get();

    foreach ($branches as $branch) {
        UpdateDailyReportJob::dispatch($today, $branch->id)->onQueue('reports');
    }
})->everyFiveMinutes()->name('update_daily_reports_scheduled')->withoutOverlapping();

// Generate monthly reports (runs on 1st of each month)
Schedule::call(function () {
    $lastMonth = now()->subMonth()->format('Y-m');
    UpdateMonthlyReportJob::dispatch($lastMonth)->onQueue('reports');
})->monthlyOn(1, '02:00')->name('update_monthly_reports')->withoutOverlapping();

Schedule::call(function () {
    CleanupOldFilesJob::dispatch(90)->onQueue('maintenance');
})->dailyAt('02:00')->name('cleanup_old_files')->withoutOverlapping();

// Keep failed jobs table clean
Schedule::command('queue:prune-failed --hours=168')->weekly();
