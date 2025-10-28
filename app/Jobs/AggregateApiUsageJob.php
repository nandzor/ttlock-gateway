<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ApiUsageSummary;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AggregateApiUsageJob implements ShouldQueue {
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
            $logPath = "logs/api_requests/{$this->date}.log";

            if (!Storage::disk('local')->exists($logPath)) {
                Log::info('No API log file found', ['date' => $this->date]);
                return;
            }

            $logContent = Storage::disk('local')->get($logPath);
            $lines = explode(PHP_EOL, trim($logContent));
            $aggregated = [];

            foreach ($lines as $line) {
                if (empty($line)) continue;
                $logEntry = json_decode($line, true);
                if (!$logEntry) continue;

                $key = $logEntry['api_credential_id'] . '|' . $logEntry['endpoint'] . '|' . $logEntry['method'];

                if (!isset($aggregated[$key])) {
                    $aggregated[$key] = [
                        'api_credential_id' => $logEntry['api_credential_id'],
                        'endpoint' => $logEntry['endpoint'],
                        'method' => $logEntry['method'],
                        'total_requests' => 0,
                        'success_requests' => 0,
                        'error_requests' => 0,
                        'response_times' => [],
                        'query_counts' => [],
                        'memory_usages' => [],
                    ];
                }

                $aggregated[$key]['total_requests']++;

                if ($logEntry['response_status'] >= 200 && $logEntry['response_status'] < 300) {
                    $aggregated[$key]['success_requests']++;
                } else {
                    $aggregated[$key]['error_requests']++;
                }

                $aggregated[$key]['response_times'][] = $logEntry['response_time_ms'];
                $aggregated[$key]['query_counts'][] = $logEntry['query_count'];
                $aggregated[$key]['memory_usages'][] = $logEntry['memory_usage_mb'] * 1024 * 1024; // Convert to bytes
            }

            foreach ($aggregated as $data) {
                ApiUsageSummary::updateOrCreate(
                    [
                        'api_credential_id' => $data['api_credential_id'],
                        'summary_date' => $this->date,
                        'endpoint' => $data['endpoint'],
                        'method' => $data['method'],
                    ],
                    [
                        'total_requests' => $data['total_requests'],
                        'success_requests' => $data['success_requests'],
                        'error_requests' => $data['error_requests'],
                        'avg_response_time_ms' => (int) round(array_sum($data['response_times']) / count($data['response_times'])),
                        'max_response_time_ms' => (int) max($data['response_times']),
                        'min_response_time_ms' => (int) min($data['response_times']),
                        'avg_query_count' => (int) round(array_sum($data['query_counts']) / count($data['query_counts'])),
                        'max_query_count' => (int) max($data['query_counts']),
                        'avg_memory_usage' => (int) round(array_sum($data['memory_usages']) / count($data['memory_usages'])),
                        'max_memory_usage' => (int) max($data['memory_usages']),
                    ]
                );
            }

            Log::info('API usage aggregation completed', [
                'date' => $this->date,
                'total_entries' => count($lines),
                'unique_endpoints' => count($aggregated)
            ]);
        } catch (\Exception $e) {
            Log::error('API usage aggregation failed', [
                'date' => $this->date,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function tags(): array {
        return ['aggregation', 'api-usage', 'date:' . $this->date];
    }
}
