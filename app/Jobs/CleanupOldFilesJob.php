<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\StorageFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupOldFilesJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $timeout = 600;
    public int $daysToKeep;

    public function __construct(int $daysToKeep = 90) {
        $this->daysToKeep = $daysToKeep;
        $this->onQueue('maintenance');
    }

    public function handle(): void {
        try {
            $cutoffDate = Carbon::now()->subDays($this->daysToKeep);
            $deletedCount = 0;

            // Cleanup old storage files
            $oldFiles = StorageFile::where('created_at', '<', $cutoffDate)->get();

            foreach ($oldFiles as $file) {
                if (Storage::disk($file->storage_disk)->exists($file->file_path)) {
                    Storage::disk($file->storage_disk)->delete($file->file_path);
                }
                $file->delete();
                $deletedCount++;
            }

            // Cleanup old log files
            $this->cleanupLogFiles('api_requests', $cutoffDate);
            $this->cleanupLogFiles('whatsapp_messages', $cutoffDate);

            Log::info('Old files cleanup completed', [
                'days_to_keep' => $this->daysToKeep,
                'deleted_files' => $deletedCount,
                'cutoff_date' => $cutoffDate->toDateString()
            ]);
        } catch (\Exception $e) {
            Log::error('Old files cleanup failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function cleanupLogFiles(string $logType, Carbon $cutoffDate): void {
        $logDirectory = "logs/{$logType}";
        $files = Storage::disk('local')->files($logDirectory);

        foreach ($files as $file) {
            $fileDate = basename($file, '.log');

            try {
                $date = Carbon::createFromFormat('Y-m-d', $fileDate);

                if ($date->lt($cutoffDate)) {
                    Storage::disk('local')->delete($file);
                    Log::info("Deleted old log file: {$file}");
                }
            } catch (\Exception $e) {
                Log::warning("Could not parse date from log file: {$file}");
            }
        }
    }

    public function tags(): array {
        return ['maintenance', 'cleanup'];
    }
}
