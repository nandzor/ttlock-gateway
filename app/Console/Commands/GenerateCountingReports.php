<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReIdBranchDetection;
use App\Models\CountingReport;
use Carbon\Carbon;

class GenerateCountingReports extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:generate-counting
                          {--date= : Specific date to generate (Y-m-d format)}
                          {--days=1 : Number of past days to generate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily counting reports from detection data';

    /**
     * Execute the console command.
     */
    public function handle() {
        $this->info('🔄 Starting counting report generation...');

        $specificDate = $this->option('date');
        $days = (int) $this->option('days');

        if ($specificDate) {
            // Generate for specific date
            $dates = [Carbon::parse($specificDate)];
            $this->info("Generating report for: {$specificDate}");
        } else {
            // Generate for past N days
            $dates = [];
            for ($i = 0; $i < $days; $i++) {
                $dates[] = Carbon::now()->subDays($i);
            }
            $this->info("Generating reports for past {$days} day(s)");
        }

        $totalGenerated = 0;

        foreach ($dates as $date) {
            $dateString = $date->format('Y-m-d');
            $this->info("\n📅 Processing date: {$dateString}");

            $generated = $this->generateReportsForDate($dateString);
            $totalGenerated += $generated;

            $this->info("  ✓ Generated {$generated} reports");
        }

        $this->newLine();
        $this->info("✅ Total reports generated/updated: {$totalGenerated}");
        $this->info('🎉 Counting report generation completed!');

        return Command::SUCCESS;
    }

    /**
     * Generate reports for a specific date
     */
    private function generateReportsForDate(string $date): int {
        $startOfDay = Carbon::parse($date)->startOfDay();
        $endOfDay = Carbon::parse($date)->endOfDay();

        // Get detections for this date
        $detections = ReIdBranchDetection::whereBetween('detection_timestamp', [$startOfDay, $endOfDay])->get();

        if ($detections->isEmpty()) {
            $this->warn("  ⚠ No detections found for {$date}");
            return 0;
        }

        $reportCount = 0;

        // Generate branch-specific reports
        $groupedByBranch = $detections->groupBy('branch_id');

        foreach ($groupedByBranch as $branchId => $items) {
            $uniqueDevices = $items->pluck('device_id')->unique()->count();
            $totalDetections = $items->count();
            $uniquePersons = $items->pluck('re_id')->unique()->count();

            CountingReport::updateOrCreate(
                [
                    'branch_id' => $branchId,
                    'report_type' => 'daily',
                    'report_date' => $date,
                ],
                [
                    'total_devices' => $uniqueDevices,
                    'total_detections' => $totalDetections,
                    'total_events' => $totalDetections,
                    'unique_person_count' => $uniquePersons,
                    'report_data' => [
                        'detection_breakdown' => [
                            'total' => $totalDetections,
                            'unique_persons' => $uniquePersons,
                            'devices_active' => $uniqueDevices,
                        ],
                        'generated_at' => now()->toISOString(),
                    ],
                ]
            );

            $reportCount++;
        }

        // Generate overall report (all branches combined)
        $uniqueDevices = $detections->pluck('device_id')->unique()->count();
        $totalDetections = $detections->count();
        $uniquePersons = $detections->pluck('re_id')->unique()->count();
        $uniqueBranches = $detections->pluck('branch_id')->unique()->count();

        CountingReport::updateOrCreate(
            [
                'branch_id' => null,
                'report_type' => 'daily',
                'report_date' => $date,
            ],
            [
                'total_devices' => $uniqueDevices,
                'total_detections' => $totalDetections,
                'total_events' => $totalDetections,
                'unique_person_count' => $uniquePersons,
                'report_data' => [
                    'overall' => true,
                    'total_branches' => $uniqueBranches,
                    'detection_breakdown' => [
                        'total' => $totalDetections,
                        'unique_persons' => $uniquePersons,
                        'devices_active' => $uniqueDevices,
                        'branches_active' => $uniqueBranches,
                    ],
                    'generated_at' => now()->toISOString(),
                ],
            ]
        );

        $reportCount++;

        return $reportCount;
    }
}
