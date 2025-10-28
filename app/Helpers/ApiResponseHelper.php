<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ApiResponseHelper {
    /**
     * Success response
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    public static function success($data = null, string $message = 'Operation completed successfully', int $statusCode = 200): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => self::getMeta(),
        ], $statusCode);
    }

    /**
     * Error response
     *
     * @param string $message
     * @param string $code
     * @param mixed $details
     * @param int $statusCode
     * @return JsonResponse
     */
    public static function error(string $message, string $code = 'ERROR', $details = null, int $statusCode = 400): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
            'error' => [
                'code' => $code,
            ],
            'meta' => self::getMeta(),
        ];

        if ($details !== null) {
            $response['error']['details'] = $details;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Validation error response
     *
     * @param array $errors
     * @param string $message
     * @return JsonResponse
     */
    public static function validationError(array $errors, string $message = 'Validation failed'): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'details' => $errors,
            ],
            'meta' => self::getMeta(),
        ], 422);
    }

    /**
     * Paginated response
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    public static function paginated($data, string $message = 'Data retrieved successfully'): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'last_page' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ],
            'meta' => self::getMeta(),
        ], 200);
    }

    /**
     * Not found response
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function notFound(string $message = 'Resource not found'): JsonResponse {
        return self::error($message, 'NOT_FOUND', null, 404);
    }

    /**
     * Unauthorized response
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse {
        return self::error($message, 'UNAUTHORIZED', null, 401);
    }

    /**
     * Forbidden response
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function forbidden(string $message = 'Forbidden'): JsonResponse {
        return self::error($message, 'FORBIDDEN', null, 403);
    }

    /**
     * Server error response
     *
     * @param string $message
     * @param mixed $details
     * @return JsonResponse
     */
    public static function serverError(string $message = 'Internal server error', $details = null): JsonResponse {
        return self::error($message, 'SERVER_ERROR', $details, 500);
    }

    /**
     * Accepted response (for async processing)
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    public static function accepted($data = null, string $message = 'Request accepted for processing'): JsonResponse {
        return self::success($data, $message, 202);
    }

    /**
     * Get meta information
     *
     * @return array
     */
    private static function getMeta(): array {
        $meta = [
            'timestamp' => now()->toIso8601String(),
            'version' => '1.0',
            'request_id' => request()->header('X-Request-ID', (string) \Illuminate\Support\Str::uuid()),
        ];

        // Add performance metrics
        if (config('app.performance_monitoring.enabled', true)) {
            $meta['query_count'] = self::getQueryCount();
            $meta['memory_usage'] = self::getMemoryUsage();
            $meta['execution_time'] = self::getExecutionTime();
        }

        return $meta;
    }

    /**
     * Get query count
     *
     * @return int
     */
    private static function getQueryCount(): int {
        if (config('app.log_queries', false)) {
            return count(DB::getQueryLog());
        }
        return 0;
    }

    /**
     * Get memory usage
     *
     * @return string
     */
    private static function getMemoryUsage(): string {
        $bytes = memory_get_usage();
        $mb = round($bytes / 1024 / 1024, 2);
        return $mb . ' MB';
    }

    /**
     * Get execution time in milliseconds
     *
     * @return string
     */
    private static function getExecutionTime(): string {
        if (defined('LARAVEL_START')) {
            $time = microtime(true) - LARAVEL_START;
            return round($time * 1000, 2) . 'ms';
        }
        return '0ms';
    }
}
