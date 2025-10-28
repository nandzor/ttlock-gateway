<?php

namespace App\Console\Commands;

use App\Jobs\ProcessCCTVData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;

class TestQueue extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:test {--count=5 : Number of jobs to dispatch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test queue by dispatching sample jobs';

    /**
     * Execute the console command.
     */
    public function handle() {
        $count = $this->option('count');

        $this->info("Dispatching {$count} test jobs to queue...");

        for ($i = 1; $i <= $count; $i++) {
            $data = [
                'id' => $i,
                'camera_id' => 'CAM_' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'timestamp' => now()->toISOString(),
                'data' => [
                    'motion_detected' => rand(0, 1),
                    'face_count' => rand(0, 5),
                    'confidence' => rand(70, 99) / 100
                ]
            ];

            ProcessCCTVData::dispatch($data);
            $this->line("Job {$i} dispatched");
        }

        $this->info("✅ {$count} jobs dispatched successfully!");
        $this->line("Check logs with: docker compose logs queue_worker");
    }
}
