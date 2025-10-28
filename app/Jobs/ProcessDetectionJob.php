<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ReIdMaster;
use App\Models\ReIdBranchDetection;
use App\Models\EventLog;
use App\Jobs\ProcessDetectionImageJob;
use App\Jobs\SendWhatsAppNotificationJob;
use App\Jobs\UpdateDailyReportJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessDetectionJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;
    public $backoff = [10, 30, 60];

    public string $reId;
    public int $branchId;
    public string $deviceId;
    public int $detectedCount;
    public array $detectionData;
    public ?string $imagePath;
    public string $jobId;

    public function __construct(
        string $reId,
        int $branchId,
        string $deviceId,
        int $detectedCount,
        array $detectionData,
        ?string $imagePath,
        string $jobId
    ) {
        $this->reId = $reId;
        $this->branchId = $branchId;
        $this->deviceId = $deviceId;
        $this->detectedCount = $detectedCount;
        $this->detectionData = $detectionData;
        $this->imagePath = $imagePath;
        $this->jobId = $jobId;
        $this->onQueue('detections');
    }

    public function handle(): void {
        try {
            DB::transaction(function () {
                // 1. Create/Update re_id_masters (daily unique by re_id + date)
                $today = now()->toDateString();
                $detectionTime = now();

                $reIdMaster = ReIdMaster::firstOrCreate(
                    [
                        're_id' => $this->reId,
                        'detection_date' => $today,
                    ],
                    [
                        'appearance_features' => $this->detectionData['appearance_features'] ?? [],
                        'detection_time' => $detectionTime,
                        'total_detection_branch_count' => 1,
                        'total_actual_count' => $this->detectedCount,
                        'first_detected_at' => $detectionTime,
                        'last_detected_at' => $detectionTime,
                    ]
                );

                // Check if this branch already detected this person today (before creating new record)
                $existingBranchDetection = ReIdBranchDetection::where('re_id', $this->reId)
                    ->where('branch_id', $this->branchId)
                    ->whereDate('detection_timestamp', $today)
                    ->exists();

                // Update counters if not newly created
                if (!$reIdMaster->wasRecentlyCreated) {
                    // Only increment branch count if this is a new branch detection
                    if (!$existingBranchDetection) {
                        $reIdMaster->increment('total_detection_branch_count');
                    }

                    // Always increment actual count (total detections)
                    $reIdMaster->increment('total_actual_count', $this->detectedCount);

                    // Update timestamps - only update first_detected_at if this is earlier
                    $updateData = ['last_detected_at' => $detectionTime];
                    if ($detectionTime < $reIdMaster->first_detected_at) {
                        $updateData['first_detected_at'] = $detectionTime;
                    }
                    $reIdMaster->update($updateData);
                }

                // 2. Log detection in re_id_branch_detections
                ReIdBranchDetection::create([
                    're_id' => $this->reId,
                    'branch_id' => $this->branchId,
                    'device_id' => $this->deviceId,
                    'detection_timestamp' => $detectionTime,
                    'detected_count' => $this->detectedCount,
                    'detection_data' => $this->detectionData,
                ]);

                // 3. Create event log
                $eventLog = EventLog::create([
                    'branch_id' => $this->branchId,
                    'device_id' => $this->deviceId,
                    're_id' => $this->reId,
                    'event_type' => 'detection',
                    'detected_count' => $this->detectedCount,
                    'image_path' => $this->imagePath,
                    'image_sent' => false,
                    'message_sent' => false,
                    'notification_sent' => false,
                    'event_data' => $this->detectionData,
                    'event_timestamp' => $detectionTime,
                ]);

                Log::info('Detection processed successfully', [
                    're_id' => $this->reId,
                    'branch_id' => $this->branchId,
                    'device_id' => $this->deviceId,
                    'event_log_id' => $eventLog->id,
                ]);

                // 4. Dispatch child jobs (async chain)
                if ($this->imagePath) {
                    ProcessDetectionImageJob::dispatch($this->imagePath, $eventLog->id)
                        ->onQueue('images')
                        ->delay(now()->addSeconds(2));
                }

                SendWhatsAppNotificationJob::dispatch($eventLog->id)
                    ->onQueue('notifications')
                    ->delay(now()->addSeconds(5));

                // UpdateDailyReportJob moved to scheduler (every 5 minutes)
                // UpdateDailyReportJob::dispatch($today, $this->branchId)
                //     ->onQueue('reports')
                //     ->delay(now()->addMinutes(5));
            }, 5); // Retry transaction up to 5 times on deadlock

        } catch (\Exception $e) {
            Log::error('Detection processing failed', [
                're_id' => $this->reId,
                'branch_id' => $this->branchId,
                'device_id' => $this->deviceId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void {
        Log::error('Detection job failed permanently', [
            're_id' => $this->reId,
            'branch_id' => $this->branchId,
            'device_id' => $this->deviceId,
            'error' => $exception->getMessage(),
        ]);
    }

    public function tags(): array {
        return ['detection', 'branch:' . $this->branchId, 'device:' . $this->deviceId];
    }
}
