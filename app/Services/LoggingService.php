<?php

namespace App\Services;

use App\Models\StorageFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class LoggingService {
    public static function logWhatsAppMessage(array $data): bool {
        try {
            $logData = [
                'timestamp' => now()->toIso8601String(),
                'event_log_id' => $data['event_log_id'] ?? null,
                'branch_id' => $data['branch_id'],
                'device_id' => $data['device_id'],
                're_id' => $data['re_id'] ?? null,
                'phone_number' => $data['phone_number'],
                'message_text' => $data['message_text'],
                'image_path' => $data['image_path'] ?? null,
                'status' => $data['status'] ?? 'pending',
                'provider_response' => $data['provider_response'] ?? null,
                'error_message' => $data['error_message'] ?? null,
                'retry_count' => $data['retry_count'] ?? 0,
            ];

            self::writeToDailyLogFile('whatsapp_messages', $logData);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to log WhatsApp message', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public static function logStorageFile(array $data): ?StorageFile {
        try {
            return StorageFile::create($data);
        } catch (\Exception $e) {
            Log::error('Failed to log storage file', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private static function writeToDailyLogFile(string $logType, array $logData): void {
        $date = now()->toDateString();
        $logPath = "logs/{$logType}/{$date}.log";
        $jsonLine = json_encode($logData, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        Storage::disk('local')->append($logPath, $jsonLine);
    }
}
