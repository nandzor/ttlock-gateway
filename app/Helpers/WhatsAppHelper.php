<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppHelper {
    /**
     * Send WhatsApp message via WAHA API
     */
    public static function sendMessage(string $phoneNumber, string $message, ?string $imagePath = null, array $metadata = []): array {
        try {
            $wahaUrl = config('whatsapp.waha_url', 'http://localhost:3000');
            $sessionId = config('whatsapp.session_id', 'default');

            // Format phone number (remove + and ensure it starts with country code)
            $formattedNumber = self::formatPhoneNumber($phoneNumber);

            $payload = [
                'chatId' => $formattedNumber . '@c.us',
                'text' => $message,
                'session' => $sessionId,
            ];

            // Add image if provided
            if ($imagePath && file_exists(storage_path('app/' . $imagePath))) {
                $imageUrl = self::uploadImageToWaha($imagePath, $wahaUrl, $sessionId);
                if ($imageUrl) {
                    $payload['image'] = $imageUrl;
                }
            }

            // Send message via WAHA
            $response = Http::timeout(30)->post("{$wahaUrl}/api/sendMessage", $payload);

            if ($response->successful()) {
                $responseData = $response->json();

                Log::info('WhatsApp message sent successfully', [
                    'phone_number' => $phoneNumber,
                    'formatted_number' => $formattedNumber,
                    'message_id' => $responseData['id'] ?? null,
                    'metadata' => $metadata,
                ]);

                return [
                    'success' => true,
                    'message_id' => $responseData['id'] ?? uniqid('wa_'),
                    'phone_number' => $phoneNumber,
                    'response' => $responseData,
                ];
            } else {
                Log::error('WhatsApp message failed', [
                    'phone_number' => $phoneNumber,
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'metadata' => $metadata,
                ]);

                return [
                    'success' => false,
                    'error' => 'WAHA API error: ' . $response->status(),
                    'phone_number' => $phoneNumber,
                    'response' => $response->body(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp message exception', [
                'phone_number' => $phoneNumber,
                'error' => $e->getMessage(),
                'metadata' => $metadata,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'phone_number' => $phoneNumber,
            ];
        }
    }

    /**
     * Upload image to WAHA and get URL
     */
    private static function uploadImageToWaha(string $imagePath, string $wahaUrl, string $sessionId): ?string {
        try {
            $fullPath = storage_path('app/' . $imagePath);

            if (!file_exists($fullPath)) {
                Log::warning('Image file not found for WAHA upload', ['path' => $imagePath]);
                return null;
            }

            $response = Http::timeout(30)
                ->attach('file', file_get_contents($fullPath), basename($imagePath))
                ->post("{$wahaUrl}/api/sendFile", [
                    'session' => $sessionId,
                ]);

            if ($response->successful()) {
                $responseData = $response->json();
                return $responseData['url'] ?? null;
            }

            Log::error('Failed to upload image to WAHA', [
                'path' => $imagePath,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exception uploading image to WAHA', [
                'path' => $imagePath,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Format phone number for WhatsApp
     */
    private static function formatPhoneNumber(string $phoneNumber): string {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);

        // If starts with 0, replace with country code (assuming Indonesia +62)
        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62' . substr($cleaned, 1);
        }

        // If doesn't start with country code, add +62 (Indonesia)
        if (!str_starts_with($cleaned, '62')) {
            $cleaned = '62' . $cleaned;
        }

        return $cleaned;
    }

    /**
     * Check WAHA session status
     */
    public static function checkSessionStatus(): array {
        try {
            $wahaUrl = config('whatsapp.waha_url', 'http://localhost:3000');
            $sessionId = config('whatsapp.session_id', 'default');

            $response = Http::timeout(10)->get("{$wahaUrl}/api/sessions/{$sessionId}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to check session status',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Start WAHA session
     */
    public static function startSession(): array {
        try {
            $wahaUrl = config('whatsapp.waha_url', 'http://localhost:3000');
            $sessionId = config('whatsapp.session_id', 'default');

            $response = Http::timeout(30)->post("{$wahaUrl}/api/sessions/{$sessionId}/start");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'response' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to start session',
                'response' => $response->body(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
