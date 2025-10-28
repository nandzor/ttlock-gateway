<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\CountingReport;
use App\Models\CompanyBranch;
use App\Models\DeviceMaster;
use App\Models\EventLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UpdateMonthlyReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;
    public string $month;
    public ?int $branchId;

    public function __construct(string $month, ?int $branchId = null)
    {
        $this->month = $month;
        $this->branchId = $branchId;
        $this->onQueue('reports');
    }

    public function handle(): void
    {
        try {
            if ($this->branchId) {
                $this->generateBranchMonthlyReport($this->branchId);
            } else {
                $this->generateOverallMonthlyReport();
                // Generate for each branch
                CompanyBranch::active()->each(function ($branch) {
                    $this->generateBranchMonthlyReport($branch->id);
                });
            }

            Log::info('Monthly report updated', [
                'month' => $this->month,
                'branch_id' => $this->branchId
            ]);
        } catch (\Exception $e) {
            Log::error('Monthly report update failed', [
                'month' => $this->month,
                'branch_id' => $this->branchId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function generateBranchMonthlyReport(int $branchId): void
    {
        $startDate = Carbon::parse($this->month . '-01')->startOfMonth();
        $endDate = Carbon::parse($this->month . '-01')->endOfMonth();

        // Get daily reports for the month
        $dailyReports = CountingReport::where('report_type', 'daily')
            ->where('branch_id', $branchId)
            ->whereBetween('report_date', [$startDate, $endDate])
            ->get();

        if ($dailyReports->isEmpty()) {
            Log::warning("No daily reports found for branch {$branchId} in {$this->month}");
            return;
        }

        $totalDetections = $dailyReports->sum('total_detections');
        $totalEvents = $dailyReports->sum('total_events');
        $uniquePersons = $dailyReports->max('unique_person_count');
        $totalDevices = $dailyReports->max('total_devices');
        $reportDays = $dailyReports->count();

        $monthlyData = [
            'monthly_summary' => [
                'total_detections' => $totalDetections,
                'total_events' => $totalEvents,
                'unique_persons' => $uniquePersons,
                'total_devices' => $totalDevices,
                'report_days' => $reportDays,
                'avg_detections_per_day' => $reportDays > 0 ? round($totalDetections / $reportDays, 2) : 0,
            ],
            'daily_breakdown' => $dailyReports->map(function ($report) {
                return [
                    'date' => $report->report_date->format('Y-m-d'),
                    'detections' => $report->total_detections,
                    'unique_persons' => $report->unique_person_count,
                    'devices' => $report->total_devices,
                ];
            })->toArray(),
            'generated_at' => now()->toISOString(),
        ];

        CountingReport::updateOrCreate(
            [
                'report_type' => 'monthly',
                'report_date' => $startDate,
                'branch_id' => $branchId,
            ],
            [
                'total_devices' => $totalDevices,
                'total_detections' => $totalDetections,
                'total_events' => $totalEvents,
                'unique_person_count' => $uniquePersons,
                'report_data' => $monthlyData,
            ]
        );
    }

    private function generateOverallMonthlyReport(): void
    {
        $startDate = Carbon::parse($this->month . '-01')->startOfMonth();
        $endDate = Carbon::parse($this->month . '-01')->endOfMonth();

        // Get daily reports for the month (overall)
        $dailyReports = CountingReport::where('report_type', 'daily')
            ->whereNull('branch_id')
            ->whereBetween('report_date', [$startDate, $endDate])
            ->get();

        if ($dailyReports->isEmpty()) {
            Log::warning("No daily reports found for overall in {$this->month}");
            return;
        }

        $totalDetections = $dailyReports->sum('total_detections');
        $totalEvents = $dailyReports->sum('total_events');
        $uniquePersons = $dailyReports->max('unique_person_count');
        $totalDevices = $dailyReports->max('total_devices');
        $reportDays = $dailyReports->count();

        $monthlyData = [
            'monthly_summary' => [
                'total_detections' => $totalDetections,
                'total_events' => $totalEvents,
                'unique_persons' => $uniquePersons,
                'total_devices' => $totalDevices,
                'report_days' => $reportDays,
                'avg_detections_per_day' => $reportDays > 0 ? round($totalDetections / $reportDays, 2) : 0,
            ],
            'daily_breakdown' => $dailyReports->map(function ($report) {
                return [
                    'date' => $report->report_date->format('Y-m-d'),
                    'detections' => $report->total_detections,
                    'unique_persons' => $report->unique_person_count,
                    'devices' => $report->total_devices,
                ];
            })->toArray(),
            'generated_at' => now()->toISOString(),
        ];

        CountingReport::updateOrCreate(
            [
                'report_type' => 'monthly',
                'report_date' => $startDate,
                'branch_id' => null,
            ],
            [
                'total_devices' => $totalDevices,
                'total_detections' => $totalDetections,
                'total_events' => $totalEvents,
                'unique_person_count' => $uniquePersons,
                'report_data' => $monthlyData,
            ]
        );
    }

    public function tags(): array
    {
        return ['reports', 'monthly', 'month:' . $this->month];
    }
}
