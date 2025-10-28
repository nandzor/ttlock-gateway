<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\EventLog;
use App\Models\BranchEventSetting;
use App\Helpers\WhatsAppHelper;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotificationJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    public $timeout = 60;
    public $backoff = [30, 60, 120, 300, 600]; // Exponential backoff

    public int $eventLogId;

    public function __construct(int $eventLogId) {
        $this->eventLogId = $eventLogId;
        $this->onQueue('notifications');
    }

    public function handle(): void {
        try {
            $eventLog = EventLog::with(['branch', 'device', 'reIdMaster'])->find($this->eventLogId);

            if (!$eventLog) {
                Log::warning('Event log not found', ['event_log_id' => $this->eventLogId]);
                return;
            }

            // Get branch event settings
            $settings = BranchEventSetting::where('branch_id', $eventLog->branch_id)
                ->where('device_id', $eventLog->device_id)
                ->where('is_active', true)
                ->where('whatsapp_enabled', true)
                ->first();

            if (!$settings || empty($settings->whatsapp_numbers)) {
                Log::info('WhatsApp not enabled or no numbers configured', [
                    'branch_id' => $eventLog->branch_id,
                    'device_id' => $eventLog->device_id,
                ]);
                return;
            }

            // Prepare message from template
            $message = $settings->formatMessage([
                'branch_name' => $eventLog->branch->branch_name ?? 'Unknown',
                'device_name' => $eventLog->device->device_name ?? 'Unknown',
                'device_id' => $eventLog->device_id,
                're_id' => $eventLog->re_id,
                'person_name' => $eventLog->reIdMaster->person_name ?? 'Unknown Person',
                'detected_count' => $eventLog->detected_count,
                'timestamp' => $eventLog->event_timestamp->format('Y-m-d H:i:s'),
                'date' => $eventLog->event_timestamp->format('Y-m-d'),
                'time' => $eventLog->event_timestamp->format('H:i:s'),
            ]);

            // Send to each phone number
            $sentCount = 0;
            $failedCount = 0;

            foreach ($settings->whatsapp_numbers as $phoneNumber) {
                $result = WhatsAppHelper::sendMessage(
                    $phoneNumber,
                    $message,
                    $eventLog->image_path,
                    [
                        'event_log_id' => $eventLog->id,
                        'branch_id' => $eventLog->branch_id,
                        'device_id' => $eventLog->device_id,
                        're_id' => $eventLog->re_id,
                    ]
                );

                if ($result['success']) {
                    $sentCount++;
                } else {
                    $failedCount++;
                }
            }

            // Update event log
            $eventLog->update([
                'notification_sent' => $sentCount > 0,
                'message_sent' => $sentCount > 0,
                'image_sent' => $eventLog->image_path ? true : false,
            ]);

            Log::info('WhatsApp notifications sent', [
                'event_log_id' => $eventLog->id,
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp notification job failed', [
                'event_log_id' => $this->eventLogId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void {
        Log::error('WhatsApp notification job failed permanently', [
            'event_log_id' => $this->eventLogId,
            'error' => $exception->getMessage(),
        ]);
    }

    public function tags(): array {
        return ['whatsapp', 'notification', 'event:' . $this->eventLogId];
    }
}
