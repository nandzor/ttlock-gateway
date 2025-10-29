<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
// use App\Models\ApiCredential; // Model not implemented yet

class RequestResponseInterceptor {
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // Generate unique request ID
        $requestId = Str::uuid()->toString();

        // Capture original request payload BEFORE merging request_id
        $originalPayload = $request->all();

        // Add request_id to request for tracking
        $request->merge(['request_id' => $requestId]);

        // Always enable query logging for API requests to track query count
        DB::enableQueryLog();

        $response = $next($request);

        // Log API requests only
        if ($request->is('api/*')) {
            $this->logApiRequest($request, $response, $startTime, $startMemory, $requestId, $originalPayload);
        }

        return $response;
    }

    /**
     * Log API request to daily file
     */
    private function logApiRequest($request, $response, $startTime, $startMemory, $requestId, $originalPayload = []) {
        try {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2); // ms
            $memoryUsage = memory_get_usage() - $startMemory;

            // Get query count from response meta if available, otherwise count from DB log
            $queryCount = 0;
            if (method_exists($response, 'getData')) {
                $responseData = $response->getData(true);
                if (isset($responseData['meta']['query_count'])) {
                    $queryCount = $responseData['meta']['query_count'];
                }
            }

            // Fallback to DB query log count
            if ($queryCount === 0) {
                $queryLog = DB::getQueryLog();
                $queryCount = count($queryLog);
            }

            $apiCredential = $this->getApiCredential($request);
            $user = $this->getUser($request);

            // Capture response payload
            $responsePayload = $this->getResponsePayload($response);

            $logData = [
                'request_id' => $requestId,
                'timestamp' => now()->toIso8601String(),
                'api_secret' => $apiCredential?->api_secret ? substr($apiCredential->api_secret, 0, 10) . '***' : null,
                'api_key' => $apiCredential?->api_key ? substr($apiCredential->api_key, 0, 10) . '***' : null,
                'user_id' => $user?->id,
                'user_name' => $user?->name,
                'user_email' => $user?->email,
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'request_payload' => $this->sanitizePayload($originalPayload),
                'request_headers' => $this->sanitizeHeaders($request->headers->all()),
                'response_status' => $response->getStatusCode(),
                'response_payload' => $responsePayload,
                'response_headers' => $this->sanitizeHeaders($response->headers->all()),
                'response_time_ms' => (int) $executionTime,
                'query_count' => $queryCount,
                'memory_usage_mb' => round($memoryUsage / 1024 / 1024, 2),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ];

            // Write to daily log file (instant, non-blocking)
            $this->writeToDailyLogFile('api_requests', $logData);

            // Performance alerts
            if (config('app.performance_monitoring.enabled', true)) {
                $slowThreshold = config('app.performance_monitoring.slow_query_threshold', 1000);
                $memoryThreshold = config('app.performance_monitoring.high_memory_threshold', 128);

                if ($executionTime > $slowThreshold) {
                    Log::warning('Slow API request detected', [
                        'endpoint' => $request->path(),
                        'execution_time' => $executionTime . 'ms',
                        'query_count' => $queryCount,
                    ]);
                }

                if (($memoryUsage / 1024 / 1024) > $memoryThreshold) {
                    Log::warning('High memory usage detected', [
                        'endpoint' => $request->path(),
                        'memory_usage' => round($memoryUsage / 1024 / 1024, 2) . 'MB',
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to log API request', [
                'error' => $e->getMessage(),
                'endpoint' => $request->path()
            ]);
        }
    }

    /**
     * Write to daily log file
     */
    private function writeToDailyLogFile(string $logType, array $logData): void {
        try {
            $date = now()->toDateString(); // YYYY-MM-DD
            $logDir = storage_path("app/logs/{$logType}");
            $logFile = "{$logDir}/{$date}.log";

            // Ensure directory exists
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            // Convert to JSON (one line per request)
            $jsonLine = json_encode($logData, JSON_UNESCAPED_UNICODE) . PHP_EOL;

            // Write to file directly
            file_put_contents($logFile, $jsonLine, FILE_APPEND | LOCK_EX);
        } catch (\Exception $e) {
            Log::error('Failed to write to daily log file', [
                'error' => $e->getMessage(),
                'logFile' => $logFile ?? 'unknown',
                'logType' => $logType
            ]);
        }
    }

    /**
     * Get API credential from request
     */
    private function getApiCredential($request): ?object {
        $apiKey = $request->header('X-API-Key');

        if (!$apiKey) {
            return null;
        }

        // TODO: Implement ApiCredential model
        // For now, return null since the model doesn't exist
        return null;
    }

    /**
     * Get authenticated user from request
     */
    private function getUser($request) {
        // Try to get user from Sanctum authentication
        if (Auth::check()) {
            return Auth::user();
        }

        // Try to get user from API credential
        $apiCredential = $this->getApiCredential($request);
        if ($apiCredential && isset($apiCredential->user)) {
            return $apiCredential->user;
        }

        return null;
    }

    /**
     * Sanitize sensitive fields from payload
     */
    private function sanitizePayload(array $payload): array {
        $sensitiveFields = ['password', 'api_secret', 'token', 'credit_card', 'stream_password'];

        foreach ($sensitiveFields as $field) {
            if (isset($payload[$field])) {
                $payload[$field] = '***REDACTED***';
            }
        }

        return $payload;
    }

    /**
     * Sanitize sensitive headers
     */
    private function sanitizeHeaders(array $headers): array {
        $sensitiveHeaders = [
            'authorization', 'x-api-key', 'x-auth-token', 'cookie',
            'x-csrf-token', 'x-forwarded-for', 'x-real-ip'
        ];

        $sanitized = [];
        foreach ($headers as $key => $value) {
            $lowerKey = strtolower($key);
            if (in_array($lowerKey, $sensitiveHeaders)) {
                $sanitized[$key] = ['***REDACTED***'];
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Get response payload from response object
     */
    private function getResponsePayload($response): array {
        try {
            // For JSON responses
            if ($response instanceof \Illuminate\Http\JsonResponse) {
                $data = $response->getData(true);
                return is_array($data) ? $data : ['raw' => $data];
            }

            // For other responses, get content
            $content = $response->getContent();

            // Try to decode as JSON
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }

            // Return as string if not JSON
            return ['content' => $content];

        } catch (\Exception $e) {
            return ['error' => 'Failed to capture response payload', 'message' => $e->getMessage()];
        }
    }

    /**
     * Encrypt sensitive values for logging
     */
    private function encryptValue($value): string {
        try {
            return Crypt::encryptString($value);
        } catch (\Exception $e) {
            // Fallback to simple hash if encryption fails
            return 'encrypted_' . hash('sha256', $value . config('app.key'));
        }
    }
}
