<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Services\LoggingService;

class StorageHelper {
    public static function store(
        UploadedFile $file,
        string $disk = 'local',
        string $path = 'uploads',
        array $metadata = []
    ): array {
        try {
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $fullPath = $path . '/' . $fileName;

            $stored = Storage::disk($disk)->putFileAs($path, $file, $fileName);

            if (!$stored) {
                throw new \Exception('Failed to store file');
            }

            $fileSize = $file->getSize();

            // Log to storage_files table
            $storageFile = LoggingService::logStorageFile([
                'file_path' => $fullPath,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $fileSize,
                'storage_disk' => $disk,
                'related_table' => $metadata['related_table'] ?? null,
                'related_id' => $metadata['related_id'] ?? null,
                'metadata' => [
                    'original_name' => $file->getClientOriginalName(),
                    'extension' => $file->getClientOriginalExtension(),
                    'uploaded_at' => now()->toIso8601String(),
                ] + $metadata,
                'uploaded_by' => auth()->id(),
            ]);

            return [
                'success' => true,
                'file_path' => $fullPath,
                'file_name' => $fileName,
                'file_size' => $fileSize,
                'storage_file_id' => $storageFile?->id,
                'url' => Storage::disk($disk)->url($fullPath),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public static function delete(string $filePath, string $disk = 'local'): bool {
        try {
            if (Storage::disk($disk)->exists($filePath)) {
                return Storage::disk($disk)->delete($filePath);
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function getUrl(string $filePath, string $disk = 'local'): ?string {
        try {
            if (Storage::disk($disk)->exists($filePath)) {
                return Storage::disk($disk)->url($filePath);
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function exists(string $filePath, string $disk = 'local'): bool {
        return Storage::disk($disk)->exists($filePath);
    }
}
