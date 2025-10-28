<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

class MonitorSystem extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:system {--watch : Watch mode with real-time updates}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor FrankenPHP system status, queue, and performance';

    /**
     * Execute the console command.
     */
    public function handle() {
        if ($this->option('watch')) {
            $this->watchMode();
        } else {
            $this->showStatus();
        }
    }

    /**
     * Show current system status
     */
    private function showStatus() {
        $this->info('🔍 FrankenPHP System Monitor');
        $this->line('');

        // System Info
        $this->info('📊 System Information:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['PHP Version', PHP_VERSION],
                ['Laravel Version', app()->version()],
                ['Memory Usage', $this->formatBytes(memory_get_usage(true))],
                ['Memory Peak', $this->formatBytes(memory_get_peak_usage(true))],
                ['Timestamp', now()->toISOString()],
            ]
        );

        // Database Status
        try {
            DB::connection()->getPdo();
            $this->info('✅ Database: Connected');
        } catch (\Exception $e) {
            $this->error('❌ Database: ' . $e->getMessage());
        }

        // Redis Status
        try {
            Redis::ping();
            $this->info('✅ Redis: Connected');
        } catch (\Exception $e) {
            $this->error('❌ Redis: ' . $e->getMessage());
        }

        // Queue Status
        $this->showQueueStatus();

        // Failed Jobs
        $failedJobs = DB::table('failed_jobs')->count();
        if ($failedJobs > 0) {
            $this->warn("⚠️  Failed Jobs: {$failedJobs}");
        } else {
            $this->info('✅ Failed Jobs: 0');
        }
    }

    /**
     * Show queue status
     */
    private function showQueueStatus() {
        $this->info('📋 Queue Status:');

        $queues = ['default', 'high', 'low'];
        $queueData = [];

        foreach ($queues as $queue) {
            try {
                $size = Redis::llen("queues:{$queue}");
                $queueData[] = [$queue, $size];
            } catch (\Exception $e) {
                $queueData[] = [$queue, 'Error'];
            }
        }

        $this->table(['Queue', 'Pending Jobs'], $queueData);
    }

    /**
     * Watch mode with real-time updates
     */
    private function watchMode() {
        $this->info('🔄 Watch Mode - Press Ctrl+C to exit');
        $this->line('');

        while (true) {
            // Clear screen
            system('clear');

            $this->info('🔍 FrankenPHP System Monitor (Watch Mode)');
            $this->line('Updated: ' . now()->format('Y-m-d H:i:s'));
            $this->line('');

            // Show status
            $this->showStatus();

            // Wait 5 seconds
            sleep(5);
        }
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
