<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RefreshMaterializedViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'materialized-views:refresh {--force : Force refresh even if recently updated}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh all materialized views for optimal performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $this->info("[{$timestamp}] 🔄 Starting materialized views refresh...");
        $startTime = microtime(true);

        try {
            // Refresh re_id_branch_detections materialized views
            $this->refreshView('mv_daily_detection_stats', 'Daily detection statistics');
            $this->refreshView('mv_branch_detection_stats', 'Branch detection statistics');

            // Refresh event_logs materialized views
            $this->refreshView('mv_event_logs_daily_stats', 'Event logs daily statistics');

            // Refresh re_id_masters materialized views
            $this->refreshView('mv_re_id_masters_daily_stats', 'Re-ID masters daily statistics');

            $executionTime = (microtime(true) - $startTime) * 1000;
            $endTimestamp = now()->format('Y-m-d H:i:s');

            $this->info("[{$endTimestamp}] ✅ All materialized views refreshed successfully!");
            $this->info("[{$endTimestamp}] ⏱️  Execution time: " . round($executionTime, 2) . "ms");

            // Log the refresh
            Log::info('Materialized views refreshed via command', [
                'execution_time' => round($executionTime, 2) . 'ms',
                'timestamp' => now(),
                'command' => 'materialized-views:refresh'
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Error refreshing materialized views: " . $e->getMessage());
            Log::error('Materialized views refresh failed', [
                'error' => $e->getMessage(),
                'timestamp' => now()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Refresh a single materialized view
     */
    private function refreshView($viewName, $description)
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $this->line("[{$timestamp}] 🔄 Refreshing {$description}...");

        try {
            DB::statement("REFRESH MATERIALIZED VIEW {$viewName}");
            $this->info("[{$timestamp}] ✅ {$description} refreshed successfully");
        } catch (\Exception $e) {
            $this->error("[{$timestamp}] ❌ Failed to refresh {$description}: " . $e->getMessage());
            throw $e;
        }
    }
}
