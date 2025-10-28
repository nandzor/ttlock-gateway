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
use App\Models\ReIdMaster;
use App\Models\EventLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateDailyReportJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;
    public string $date;
    public ?int $branchId;

    public function __construct(string $date, ?int $branchId = null) {
        $this->date = $date;
        $this->branchId = $branchId;
        $this->onQueue('reports');
    }

    public function handle(): void {
        try {
            if ($this->branchId) {
                $this->generateBranchReport($this->branchId);
            } else {
                $this->generateOverallReport();
                // Generate for each branch
                CompanyBranch::active()->each(function ($branch) {
                    $this->generateBranchReport($branch->id);
                });
            }

            Log::info('Daily report updated', [
                'date' => $this->date,
                'branch_id' => $this->branchId
            ]);
        } catch (\Exception $e) {
            Log::error('Daily report update failed', [
                'date' => $this->date,
                'branch_id' => $this->branchId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function generateBranchReport(int $branchId): void {
        $totalDevices = DeviceMaster::where('branch_id', $branchId)
            ->where('status', 'active')->count();

        $detections = DB::table('re_id_branch_detections')
            ->where('branch_id', $branchId)
            ->whereDate('detection_timestamp', $this->date)
            ->selectRaw('COUNT(*) as total_detections, COUNT(DISTINCT re_id) as unique_persons, SUM(detected_count) as total_count')
            ->first();

        $totalEvents = EventLog::where('branch_id', $branchId)
            ->whereDate('event_timestamp', $this->date)
            ->count();

        CountingReport::updateOrCreate(
            [
                'report_type' => 'daily',
                'report_date' => $this->date,
                'branch_id' => $branchId,
            ],
            [
                'total_devices' => $totalDevices,
                'total_detections' => $detections->total_detections ?? 0,
                'total_events' => $totalEvents,
                'unique_person_count' => $detections->unique_persons ?? 0,
                'report_data' => [
                    'total_detected_count' => $detections->total_count ?? 0,
                ],
                'generated_at' => now(),
            ]
        );
    }

    private function generateOverallReport(): void {
        $totalDevices = DeviceMaster::active()->count();

        $detections = DB::table('re_id_branch_detections')
            ->whereDate('detection_timestamp', $this->date)
            ->selectRaw('COUNT(*) as total_detections, COUNT(DISTINCT re_id) as unique_persons, SUM(detected_count) as total_count')
            ->first();

        $totalEvents = EventLog::whereDate('event_timestamp', $this->date)->count();

        CountingReport::updateOrCreate(
            [
                'report_type' => 'daily',
                'report_date' => $this->date,
                'branch_id' => null,
            ],
            [
                'total_devices' => $totalDevices,
                'total_detections' => $detections->total_detections ?? 0,
                'total_events' => $totalEvents,
                'unique_person_count' => $detections->unique_persons ?? 0,
                'report_data' => [
                    'total_detected_count' => $detections->total_count ?? 0,
                ],
                'generated_at' => now(),
            ]
        );
    }

    public function tags(): array {
        return ['reports', 'daily', 'date:' . $this->date];
    }
}
