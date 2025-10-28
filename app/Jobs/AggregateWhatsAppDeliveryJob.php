<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\WhatsAppDeliverySummary;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AggregateWhatsAppDeliveryJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 600;
    public $backoff = [30, 60, 120];
    public string $date;

    public function __construct(string $date) {
        $this->date = $date;
        $this->onQueue('reports');
    }

    public function handle(): void {
        try {
            $logPath = "logs/whatsapp_messages/{$this->date}.log";

            if (!Storage::disk('local')->exists($logPath)) {
                Log::info('No WhatsApp log file found', ['date' => $this->date]);
                return;
            }

            $logContent = Storage::disk('local')->get($logPath);
            $lines = explode(PHP_EOL, trim($logContent));
            $aggregated = [];

            foreach ($lines as $line) {
                if (empty($line)) continue;
                $logEntry = json_decode($line, true);
                if (!$logEntry) continue;

                $key = $logEntry['branch_id'] . '|' . $logEntry['device_id'];

                if (!isset($aggregated[$key])) {
                    $aggregated[$key] = [
                        'branch_id' => $logEntry['branch_id'],
                        'device_id' => $logEntry['device_id'],
                        'total_sent' => 0,
                        'total_delivered' => 0,
                        'total_failed' => 0,
                        'total_pending' => 0,
                        'recipients' => [],
                        'messages_with_image' => 0,
                        'delivery_times' => [],
                    ];
                }

                $aggregated[$key]['total_sent']++;

                if ($logEntry['status'] === 'sent') {
                    $aggregated[$key]['total_delivered']++;
                } elseif ($logEntry['status'] === 'failed') {
                    $aggregated[$key]['total_failed']++;
                } else {
                    $aggregated[$key]['total_pending']++;
                }

                $aggregated[$key]['recipients'][] = $logEntry['phone_number'];

                if (!empty($logEntry['image_path'])) {
                    $aggregated[$key]['messages_with_image']++;
                }

                if (isset($logEntry['provider_response']['execution_time_ms'])) {
                    $aggregated[$key]['delivery_times'][] = $logEntry['provider_response']['execution_time_ms'];
                }
            }

            foreach ($aggregated as $data) {
                WhatsAppDeliverySummary::updateOrCreate(
                    [
                        'summary_date' => $this->date,
                        'branch_id' => $data['branch_id'],
                        'device_id' => $data['device_id'],
                    ],
                    [
                        'total_sent' => $data['total_sent'],
                        'total_delivered' => $data['total_delivered'],
                        'total_failed' => $data['total_failed'],
                        'total_pending' => $data['total_pending'],
                        'avg_delivery_time_ms' => !empty($data['delivery_times']) ? (int) round(array_sum($data['delivery_times']) / count($data['delivery_times'])) : null,
                        'unique_recipients' => count(array_unique($data['recipients'])),
                        'messages_with_image' => $data['messages_with_image'],
                    ]
                );
            }

            Log::info('WhatsApp delivery aggregation completed', [
                'date' => $this->date,
                'total_entries' => count($lines),
                'unique_devices' => count($aggregated)
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp delivery aggregation failed', [
                'date' => $this->date,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function tags(): array {
        return ['aggregation', 'whatsapp', 'date:' . $this->date];
    }
}
