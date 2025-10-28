<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCCTVData implements ShouldQueue {
    use Queueable, InteractsWithQueue, SerializesModels;

    public $timeout = 120;
    public $tries = 3;

    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        Log::info('Processing CCTV Data', [
            'data' => $this->data,
            'timestamp' => now(),
            'worker_id' => getmypid()
        ]);

        // Simulate processing time
        sleep(2);

        Log::info('CCTV Data processed successfully', [
            'data_id' => $this->data['id'] ?? 'unknown'
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void {
        Log::error('CCTV Data processing failed', [
            'data' => $this->data,
            'error' => $exception->getMessage()
        ]);
    }
}
