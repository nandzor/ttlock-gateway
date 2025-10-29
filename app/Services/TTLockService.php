<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Exception;

/**
 * TTLock Service
 *
 * Handles all HTTP communications with TTLock API
 * Manages OAuth2 tokens, API calls, error handling, and retry mechanisms
 */
class TTLockService extends BaseService
{
    private const BASE_URL = 'https://euapi.ttlock.com';
    private const TOKEN_CACHE_KEY = 'ttlock_access_token';
    private const TOKEN_CACHE_TTL = 3600; // 1 hour
    private const MAX_RETRIES = 3;
    private const RETRY_DELAY = 1000; // 1 second in milliseconds

    /**
     * Get OAuth2 access token
     *
     * @return array
     */
    public function getAccessToken(): array
    {
        try {
            // Check if we have a valid cached token
            $cachedToken = $this->getCachedToken();
            if ($cachedToken) {
                return $this->successResponse($cachedToken, 'Token retrieved from cache');
            }

            // Validate environment variables
            $this->validateEnvironment();

            // Prepare token request data
            $tokenData = [
                'clientId' => env('TTLOCK_CLIENT_ID'),
                'clientSecret' => env('TTLOCK_CLIENT_SECRET'),
                'username' => env('TTLOCK_USERNAME'),
                'password' => md5(env('TTLOCK_PASSWORD')), // MD5 encrypt password as required by TTLock
            ];

            Log::info('TTLock Service: Getting access token', [
                'client_id' => $tokenData['clientId'],
                'username' => $tokenData['username'],
                'timestamp' => now(),
            ]);

            // Make HTTP request to TTLock OAuth2 endpoint
            $response = $this->makeHttpRequest('/oauth2/token', $tokenData, 'POST');

            if ($response['success']) {
                $tokenData = $response['data'];

                // Cache the token
                $this->cacheToken($tokenData);

                return $this->successResponse($tokenData, 'Access token retrieved successfully');
            } else {
                return $response;
            }

        } catch (Exception $e) {
            Log::error('TTLock Service: Get access token failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Failed to get access token: ' . $e->getMessage());
        }
    }

    /**
     * Refresh OAuth2 access token
     *
     * @param string $refreshToken
     * @return array
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        try {
            $this->validateEnvironment();

            $refreshData = [
                'clientId' => env('TTLOCK_CLIENT_ID'),
                'clientSecret' => env('TTLOCK_CLIENT_SECRET'),
                'username' => env('TTLOCK_USERNAME'),
                'password' => md5(env('TTLOCK_PASSWORD')),
                'refreshToken' => $refreshToken,
            ];

            Log::info('TTLock Service: Refreshing access token', [
                'client_id' => $refreshData['clientId'],
                'timestamp' => now(),
            ]);

            $response = $this->makeHttpRequest('/oauth2/token', $refreshData, 'POST');

            if ($response['success']) {
                $tokenData = $response['data'];

                // Cache the new token
                $this->cacheToken($tokenData);

                return $this->successResponse($tokenData, 'Access token refreshed successfully');
            } else {
                return $response;
            }

        } catch (Exception $e) {
            Log::error('TTLock Service: Refresh access token failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Failed to refresh access token: ' . $e->getMessage());
        }
    }

    /**
     * Make API call to TTLock
     *
     * @param string $endpoint
     * @param array $data
     * @param string $method
     * @return array
     */
    public function makeApiCall(string $endpoint, array $data = [], string $method = 'POST'): array
    {
        try {
            // Get access token
            $tokenResponse = $this->getAccessToken();
            if (!$tokenResponse['success']) {
                return $tokenResponse;
            }

            $accessToken = $tokenResponse['data']['access_token'];

            // Add access token to request data
            $data['accessToken'] = $accessToken;

            Log::info('TTLock Service: Making API call', [
                'endpoint' => $endpoint,
                'method' => $method,
                'timestamp' => now(),
            ]);

            $response = $this->makeHttpRequest($endpoint, $data, $method);

            return $response;

        } catch (Exception $e) {
            Log::error('TTLock Service: API call failed', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('API call failed: ' . $e->getMessage());
        }
    }

    /**
     * Get configuration status
     *
     * @return array
     */
    public function getConfigStatus(): array
    {
        $config = [
            'client_id' => env('TTLOCK_CLIENT_ID') ? 'Configured' : 'Missing',
            'client_secret' => env('TTLOCK_CLIENT_SECRET') ? 'Configured' : 'Missing',
            'username' => env('TTLOCK_USERNAME') ? 'Configured' : 'Missing',
            'password' => env('TTLOCK_PASSWORD') ? 'Configured' : 'Missing',
            'api_endpoint' => self::BASE_URL . '/oauth2/token',
        ];

        $isConfigured = !in_array('Missing', $config);

        return $this->successResponse([
            'configuration' => $config,
            'is_configured' => $isConfigured,
            'status' => $isConfigured ? 'Ready' : 'Configuration Required',
        ], 'TTLock configuration status retrieved');
    }

    /**
     * Unlock TTLock
     *
     * @param string $lockId
     * @param int|null $date
     * @return array
     */
    public function unlockLock(string $lockId, int $date = null): array
    {
        try {
            // Get access token first
            $tokenResponse = $this->getAccessToken();
            if (!$tokenResponse['success']) {
                return $tokenResponse;
            }

            $accessToken = $tokenResponse['data']['access_token'];
            // TTLock requires timestamp in milliseconds and must be within ±5 minutes of server time
            $date = $date ?? round(microtime(true) * 1000);

            $data = [
                'clientId' => env('TTLOCK_CLIENT_ID'),
                'accessToken' => $accessToken,
                'lockId' => $lockId,
                'date' => $date,
            ];

            Log::info('TTLock Service: Unlocking lock', [
                'lock_id' => $lockId,
                'date' => $date,
                'timestamp' => now(),
                'date_readable' => date('Y-m-d H:i:s', $date / 1000),
            ]);

            $response = $this->makeLockApiRequest('/v3/lock/unlock', $data, 'POST');

            if ($response['success']) {
                return $this->successResponse($response['data'], 'Lock unlocked successfully');
            } else {
                return $response;
            }

        } catch (Exception $e) {
            Log::error('TTLock Service: Unlock lock failed', [
                'lock_id' => $lockId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Failed to unlock lock: ' . $e->getMessage());
        }
    }

    /**
     * Lock TTLock
     *
     * @param string $lockId
     * @param int|null $date
     * @return array
     */
    public function lockLock(string $lockId, int $date = null): array
    {
        try {
            // Get access token first
            $tokenResponse = $this->getAccessToken();
            if (!$tokenResponse['success']) {
                return $tokenResponse;
            }

            $accessToken = $tokenResponse['data']['access_token'];
            // TTLock requires timestamp in milliseconds and must be within ±5 minutes of server time
            $date = $date ?? round(microtime(true) * 1000);

            $data = [
                'clientId' => env('TTLOCK_CLIENT_ID'),
                'accessToken' => $accessToken,
                'lockId' => $lockId,
                'date' => $date,
            ];

            Log::info('TTLock Service: Locking lock', [
                'lock_id' => $lockId,
                'date' => $date,
                'timestamp' => now(),
                'date_readable' => date('Y-m-d H:i:s', $date / 1000),
            ]);

            $response = $this->makeLockApiRequest('/v3/lock/lock', $data, 'POST');

            if ($response['success']) {
                return $this->successResponse($response['data'], 'Lock locked successfully');
            } else {
                return $response;
            }

        } catch (Exception $e) {
            Log::error('TTLock Service: Lock lock failed', [
                'lock_id' => $lockId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Failed to lock lock: ' . $e->getMessage());
        }
    }

    /**
     * Get gateway status
     *
     * @return array
     */
    public function getGatewayStatus(): array
    {
        try {
            // Get access token first - if successful, gateway is online
            $tokenResponse = $this->getAccessToken();
            if (!$tokenResponse['success']) {
                // If we can't get access token, gateway is offline
                return $this->successResponse([
                    'total_gateways' => 1,
                    'online_gateways' => 0,
                    'offline_gateways' => 1,
                    'status' => 'offline',
                    'reason' => 'Cannot get access token - gateway appears offline'
                ], 'Gateway status retrieved successfully');
            }

            $accessToken = $tokenResponse['data']['access_token'];

            // Try to make a simple API call to test gateway connectivity
            $testData = [
                'clientId' => env('TTLOCK_CLIENT_ID'),
                'accessToken' => $accessToken,
            ];

            Log::info('TTLock Service: Testing gateway connectivity', [
                'timestamp' => now(),
            ]);

            // Use a simple endpoint to test connectivity
            $response = $this->makeLockApiRequest('/v3/lock/queryStatus', array_merge($testData, [
                'lockId' => env('TTLOCK_LOCKID', '17974276'),
                'date' => round(microtime(true) * 1000)
            ]), 'POST');

            // If we get any response (even error), gateway is online
            $gatewayOnline = true;
            $reason = 'Gateway is online - API accessible';

            if (!$response['success']) {
                // Check if it's a network/connectivity issue
                if (strpos($response['message'], 'timeout') !== false ||
                    strpos($response['message'], 'connection') !== false ||
                    strpos($response['message'], 'network') !== false) {
                    $gatewayOnline = false;
                    $reason = 'Gateway appears offline - network connectivity issue';
                }
            }

            return $this->successResponse([
                'total_gateways' => 1,
                'online_gateways' => $gatewayOnline ? 1 : 0,
                'offline_gateways' => $gatewayOnline ? 0 : 1,
                'status' => $gatewayOnline ? 'online' : 'offline',
                'reason' => $reason,
                'last_check' => now()->toISOString()
            ], 'Gateway status retrieved successfully');

        } catch (Exception $e) {
            Log::error('TTLock Service: Get gateway status failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // If exception occurs, consider gateway as offline
            return $this->successResponse([
                'total_gateways' => 1,
                'online_gateways' => 0,
                'offline_gateways' => 1,
                'status' => 'offline',
                'reason' => 'Gateway appears offline due to error: ' . $e->getMessage(),
                'last_check' => now()->toISOString()
            ], 'Gateway status retrieved successfully');
        }
    }

    /**
     * Get lock online/offline status
     *
     * @param string $lockId
     * @return array
     */
    public function getLockOnlineStatus(string $lockId): array
    {
        try {
            // Get access token first
            $tokenResponse = $this->getAccessToken();
            if (!$tokenResponse['success']) {
                return $tokenResponse;
            }

            $accessToken = $tokenResponse['data']['access_token'];

            // TTLock requires timestamp in milliseconds and must be within ±5 minutes of server time
            $date = round(microtime(true) * 1000);

            $data = [
                'clientId' => env('TTLOCK_CLIENT_ID'),
                'accessToken' => $accessToken,
                'lockId' => $lockId,
                'date' => $date,
            ];

            Log::info('TTLock Service: Getting lock online status', [
                'lock_id' => $lockId,
                'timestamp' => now(),
            ]);

            // Try to get lock status - if successful, lock is online
            $response = $this->makeLockApiRequest('/v3/lock/queryStatus', $data, 'POST');

            if ($response['success']) {
                $responseData = $response['data']['raw_response'] ?? [];

                // If we get a response (even with error), the lock is reachable/online
                $isOnline = true;
                $status = 'online';
                $lastSeen = now()->toISOString();

                // Check for specific error codes that might indicate offline status
                if (isset($responseData['errcode']) && $responseData['errcode'] !== 0) {
                    $errorCode = $responseData['errcode'];

                    // Some error codes might indicate the lock is offline
                    if (in_array($errorCode, [-10003, -10004, -10005])) {
                        $isOnline = false;
                        $status = 'offline';
                    }
                }

                return $this->successResponse([
                    'lock_id' => $lockId,
                    'is_online' => $isOnline,
                    'status' => $status,
                    'last_seen' => $lastSeen,
                    'response_data' => $responseData
                ], 'Lock online status retrieved successfully');
            } else {
                // If API call fails, consider lock as offline
                return $this->successResponse([
                    'lock_id' => $lockId,
                    'is_online' => false,
                    'status' => 'offline',
                    'last_seen' => null,
                    'error' => $response['message'] ?? 'API call failed'
                ], 'Lock appears to be offline');
            }

        } catch (Exception $e) {
            Log::error('TTLock Service: Get lock online status failed', [
                'lock_id' => $lockId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // If exception occurs, consider lock as offline
            return $this->successResponse([
                'lock_id' => $lockId,
                'is_online' => false,
                'status' => 'offline',
                'last_seen' => null,
                'error' => $e->getMessage()
            ], 'Lock appears to be offline due to error');
        }
    }

    /**
     * Get lock status
     *
     * @param string $lockId
     * @return array
     */
    public function getLockStatus(string $lockId): array
    {
        try {
            // Get access token first
            $tokenResponse = $this->getAccessToken();
            if (!$tokenResponse['success']) {
                return $tokenResponse;
            }

            $accessToken = $tokenResponse['data']['access_token'];

            // TTLock requires timestamp in milliseconds and must be within ±5 minutes of server time
            $date = round(microtime(true) * 1000);

            $data = [
                'clientId' => env('TTLOCK_CLIENT_ID'),
                'accessToken' => $accessToken,
                'lockId' => $lockId,
                'date' => $date,
            ];

            Log::info('TTLock Service: Getting lock status', [
                'lock_id' => $lockId,
                'timestamp' => now(),
            ]);

            $response = $this->makeLockApiRequest('/v3/lock/queryStatus', $data, 'POST');

            if ($response['success']) {
                $responseData = $response['data']['raw_response'] ?? [];

                // Check if TTLock API returned an error
                if (isset($responseData['errcode']) && $responseData['errcode'] !== 0) {
                    $errorCode = $responseData['errcode'];
                    $errorMessage = $responseData['errmsg'] ?? 'Unknown error';

                    // Handle specific error codes
                    switch ($errorCode) {
                        case -4043:
                            return $this->errorResponse('Lock does not support status query function', 400);
                        case -10001:
                            return $this->errorResponse('Invalid access token', 401);
                        case -10002:
                            return $this->errorResponse('Access token expired', 401);
                        default:
                            return $this->errorResponse("TTLock API error: {$errorMessage} (Code: {$errorCode})", 400);
                    }
                }

                return $this->successResponse($responseData, 'Lock status retrieved successfully');
            } else {
                return $response;
            }

        } catch (Exception $e) {
            Log::error('TTLock Service: Get lock status failed', [
                'lock_id' => $lockId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Failed to get lock status: ' . $e->getMessage());
        }
    }

    /**
     * Make HTTP request for lock operations
     *
     * @param string $endpoint
     * @param array $data
     * @param string $method
     * @return array
     */
    private function makeLockApiRequest(string $endpoint, array $data, string $method = 'POST'): array
    {
        $url = self::BASE_URL . $endpoint;
        $attempt = 0;

        while ($attempt < self::MAX_RETRIES) {
            try {
                $attempt++;

                Log::info("TTLock Service: Lock API request attempt {$attempt}", [
                    'url' => $url,
                    'method' => $method,
                    'data_keys' => array_keys($data),
                ]);

                $response = Http::asForm()
                    ->timeout(30)
                    ->retry(1, 1000)
                    ->$method($url, $data);

                Log::info('TTLock Service: Lock API response received', [
                    'status' => $response->status(),
                    'attempt' => $attempt,
                    'body' => $response->body(),
                    'timestamp' => now(),
                ]);

                if ($response->successful()) {
                    $responseData = $response->json();

                    return $this->successResponse([
                        'raw_response' => $responseData,
                    ], 'Lock API request successful');
                } else {
                    $errorData = $response->json();

                    Log::warning('TTLock Service: Lock API request failed', [
                        'status' => $response->status(),
                        'error' => $errorData,
                        'attempt' => $attempt,
                        'url' => $url,
                        'request_data' => $data,
                    ]);

                    // If it's a client error (4xx), don't retry
                    if ($response->status() >= 400 && $response->status() < 500) {
                        $errorMessage = 'Lock API request failed: ';
                        if (is_array($errorData)) {
                            $errorMessage .= $errorData['errmsg'] ?? $errorData['error_description'] ?? $errorData['message'] ?? 'Unknown error';
                        } else {
                            $errorMessage .= 'HTTP ' . $response->status() . ' - ' . ($errorData ?: 'Bad Request');
                        }
                        return $this->errorResponse($errorMessage, $response->status());
                    }

                    // For server errors (5xx), retry if we haven't exceeded max attempts
                    if ($attempt >= self::MAX_RETRIES) {
                        return $this->errorResponse(
                            'Lock API request failed after ' . self::MAX_RETRIES . ' attempts: ' . ($errorData['errmsg'] ?? 'Unknown error'),
                            $response->status()
                        );
                    }

                    // Wait before retry
                    usleep(self::RETRY_DELAY * 1000);
                }

            } catch (Exception $e) {
                Log::error('TTLock Service: Lock API request exception', [
                    'error' => $e->getMessage(),
                    'attempt' => $attempt,
                    'url' => $url,
                ]);

                if ($attempt >= self::MAX_RETRIES) {
                    return $this->errorResponse('Lock API request failed after ' . self::MAX_RETRIES . ' attempts: ' . $e->getMessage());
                }

                // Wait before retry
                usleep(self::RETRY_DELAY * 1000);
            }
        }

        return $this->errorResponse('Lock API request failed after maximum retries');
    }

    /**
     * Make HTTP request with retry mechanism
     *
     * @param string $endpoint
     * @param array $data
     * @param string $method
     * @return array
     */
    private function makeHttpRequest(string $endpoint, array $data, string $method = 'POST'): array
    {
        $url = self::BASE_URL . $endpoint;
        $attempt = 0;

        while ($attempt < self::MAX_RETRIES) {
            try {
                $attempt++;

                Log::info("TTLock Service: HTTP request attempt {$attempt}", [
                    'url' => $url,
                    'method' => $method,
                    'data_keys' => array_keys($data),
                ]);

                $response = Http::asForm()
                    ->timeout(30)
                    ->retry(1, 1000)
                    ->$method($url, $data);

                Log::info('TTLock Service: HTTP response received', [
                    'status' => $response->status(),
                    'attempt' => $attempt,
                    'timestamp' => now(),
                ]);

                if ($response->successful()) {
                    $responseData = $response->json();

                    return $this->successResponse([
                        'access_token' => $responseData['access_token'] ?? null,
                        'token_type' => $responseData['token_type'] ?? 'Bearer',
                        'expires_in' => $responseData['expires_in'] ?? null,
                        'refresh_token' => $responseData['refresh_token'] ?? null,
                        'scope' => $responseData['scope'] ?? null,
                        'uid' => $responseData['uid'] ?? null,
                        'openid' => $responseData['openid'] ?? null,
                        'raw_response' => $responseData,
                    ], 'Request successful');
                } else {
                    $errorData = $response->json();

                    Log::warning('TTLock Service: HTTP request failed', [
                        'status' => $response->status(),
                        'error' => $errorData,
                        'attempt' => $attempt,
                    ]);

                    // If it's a client error (4xx), don't retry
                    if ($response->status() >= 400 && $response->status() < 500) {
                        return $this->errorResponse(
                            'Request failed: ' . ($errorData['errmsg'] ?? $errorData['error_description'] ?? 'Unknown error'),
                            $response->status()
                        );
                    }

                    // For server errors (5xx), retry if we haven't exceeded max attempts
                    if ($attempt >= self::MAX_RETRIES) {
                        return $this->errorResponse(
                            'Request failed after ' . self::MAX_RETRIES . ' attempts: ' . ($errorData['errmsg'] ?? 'Unknown error'),
                            $response->status()
                        );
                    }

                    // Wait before retry
                    usleep(self::RETRY_DELAY * 1000);
                }

            } catch (Exception $e) {
                Log::error('TTLock Service: HTTP request exception', [
                    'error' => $e->getMessage(),
                    'attempt' => $attempt,
                    'url' => $url,
                ]);

                if ($attempt >= self::MAX_RETRIES) {
                    return $this->errorResponse('Request failed after ' . self::MAX_RETRIES . ' attempts: ' . $e->getMessage());
                }

                // Wait before retry
                usleep(self::RETRY_DELAY * 1000);
            }
        }

        return $this->errorResponse('Request failed after maximum retries');
    }

    /**
     * Validate required environment variables
     *
     * @throws Exception
     */
    private function validateEnvironment(): void
    {
        $requiredEnvVars = [
            'TTLOCK_CLIENT_ID',
            'TTLOCK_CLIENT_SECRET',
            'TTLOCK_USERNAME',
            'TTLOCK_PASSWORD'
        ];

        foreach ($requiredEnvVars as $envVar) {
            if (empty(env($envVar))) {
                throw new Exception("Missing required environment variable: {$envVar}");
            }
        }
    }

    /**
     * Cache access token
     *
     * @param array $tokenData
     */
    private function cacheToken(array $tokenData): void
    {
        if (isset($tokenData['access_token'])) {
            $cacheData = [
                'access_token' => $tokenData['access_token'],
                'token_type' => $tokenData['token_type'] ?? 'Bearer',
                'expires_in' => $tokenData['expires_in'] ?? 3600,
                'refresh_token' => $tokenData['refresh_token'] ?? null,
                'scope' => $tokenData['scope'] ?? null,
                'cached_at' => now()->timestamp,
            ];

            try {
                Cache::put(self::TOKEN_CACHE_KEY, $cacheData, self::TOKEN_CACHE_TTL);
            } catch (\Exception $e) {
                // Fallback to file cache if database cache fails
                $this->cacheTokenToFile($cacheData);
            }

            Log::info('TTLock Service: Token cached', [
                'expires_in' => $cacheData['expires_in'],
                'cached_at' => $cacheData['cached_at'],
            ]);
        }
    }

    /**
     * Get cached token
     *
     * @return array|null
     */
    private function getCachedToken(): ?array
    {
        try {
            $cached = Cache::get(self::TOKEN_CACHE_KEY);
        } catch (\Exception $e) {
            // Fallback to file cache if database cache fails
            $cached = $this->getCachedTokenFromFile();
        }

        if ($cached && isset($cached['access_token'])) {
            Log::info('TTLock Service: Using cached token', [
                'cached_at' => $cached['cached_at'],
                'expires_in' => $cached['expires_in'],
            ]);

            return $cached;
        }

        return null;
    }

    /**
     * Cache token to file as fallback
     *
     * @param array $cacheData
     */
    private function cacheTokenToFile(array $cacheData): void
    {
        try {
            $cacheFile = storage_path('app/ttlock_token_cache.json');
            file_put_contents($cacheFile, json_encode($cacheData));

            Log::info('TTLock Service: Token cached to file', [
                'file' => $cacheFile,
            ]);
        } catch (\Exception $e) {
            Log::warning('TTLock Service: Failed to cache token to file', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get cached token from file as fallback
     *
     * @return array|null
     */
    private function getCachedTokenFromFile(): ?array
    {
        try {
            $cacheFile = storage_path('app/ttlock_token_cache.json');

            if (file_exists($cacheFile)) {
                $content = file_get_contents($cacheFile);
                $cached = json_decode($content, true);

                if ($cached && isset($cached['access_token'])) {
                    Log::info('TTLock Service: Using cached token from file', [
                        'file' => $cacheFile,
                        'cached_at' => $cached['cached_at'],
                    ]);

                    return $cached;
                }
            }
        } catch (\Exception $e) {
            Log::warning('TTLock Service: Failed to get cached token from file', [
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Create success response
     *
     * @param array $data
     * @param string $message
     * @return array
     */
    private function successResponse(array $data, string $message): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }

    /**
     * Create error response
     *
     * @param string $message
     * @param int $statusCode
     * @return array
     */
    private function errorResponse(string $message, int $statusCode = 500): array
    {
        return [
            'success' => false,
            'message' => $message,
            'status_code' => $statusCode,
        ];
    }
}
