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
     * Get gateway list using TTLock API /v3/gateway/list
     *
     * @param int $pageNo Page number (default: 1)
     * @param int $pageSize Items per page (default: 20, max: 200)
     * @param int $orderBy Sort by: 0-by name, 1-reverse order by time, 2-reverse order by name (default: 0)
     * @return array
     */
    public function getGatewayList(int $pageNo = 1, int $pageSize = 20, int $orderBy = 0): array
    {
        try {
            $tokenResponse = $this->getAccessToken();
            if (!$tokenResponse['success']) {
                return $tokenResponse;
            }

            $accessToken = $tokenResponse['data']['access_token'];
            $date = round(microtime(true) * 1000);

            // Validate parameters
            $pageNo = max(1, $pageNo);
            $pageSize = min(200, max(1, $pageSize));
            $orderBy = in_array($orderBy, [0, 1, 2]) ? $orderBy : 0;

            $data = [
                'clientId' => env('TTLOCK_CLIENT_ID'),
                'accessToken' => $accessToken,
                'pageNo' => $pageNo,
                'pageSize' => $pageSize,
                'orderBy' => $orderBy,
                'date' => $date,
            ];

            Log::info('TTLock Service: Getting gateway list', [
                'page_no' => $pageNo,
                'page_size' => $pageSize,
                'order_by' => $orderBy,
                'timestamp' => now(),
            ]);

            $response = $this->makeLockApiRequest('/v3/gateway/list', $data, 'GET');

            if ($response['success']) {
                $responseData = $response['data']['raw_response'] ?? [];
                
                // Check if response has error code (some APIs return errcode in success response)
                if (isset($responseData['errcode']) && $responseData['errcode'] !== 0) {
                    $errorCode = $responseData['errcode'];
                    $errorMessage = $responseData['errmsg'] ?? 'Unknown error';
                    return $this->errorResponse("TTLock Gateway List API error: {$errorMessage} (Code: {$errorCode})", 400);
                }
                
                // Log the response for debugging
                Log::info('TTLock Service: Gateway list response', [
                    'response_data' => $responseData,
                    'list_count' => count($responseData['list'] ?? []),
                    'total' => $responseData['total'] ?? 0,
                ]);
                
                return $this->successResponse([
                    'raw_response' => $responseData,
                ], 'Gateway list retrieved successfully');
            } else {
                return $response;
            }

        } catch (Exception $e) {
            Log::error('TTLock Service: Get gateway list failed', [
                'page_no' => $pageNo,
                'page_size' => $pageSize,
                'order_by' => $orderBy,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->errorResponse('Failed to get gateway list: ' . $e->getMessage());
        }
    }

    /**
     * Get locks by gateway using TTLock API /v3/gateway/listLock
     *
     * @param int $gatewayId Gateway ID
     * @param int $pageNo Page number (not used, kept for compatibility)
     * @param int $pageSize Page size (not used, kept for compatibility)
     * @return array
     */
    public function getLocksByGateway(int $gatewayId, int $pageNo = 1, int $pageSize = 20): array
    {
        try {
            $tokenResponse = $this->getAccessToken();
            if (!$tokenResponse['success']) {
                return $tokenResponse;
            }

            $accessToken = $tokenResponse['data']['access_token'];
            $date = round(microtime(true) * 1000);

            // Use the correct endpoint /v3/gateway/listLock
            // This endpoint doesn't use pagination - it returns all locks related to the gateway
            $data = [
                'clientId' => env('TTLOCK_CLIENT_ID'),
                'accessToken' => $accessToken,
                'gatewayId' => $gatewayId,
                'date' => $date,
            ];

            Log::info('TTLock Service: Getting locks by gateway', [
                'gateway_id' => $gatewayId,
                'endpoint' => '/v3/gateway/listLock',
                'timestamp' => now(),
            ]);

            $response = $this->makeLockApiRequest('/v3/gateway/listLock', $data, 'GET');

            if ($response['success']) {
                $responseData = $response['data']['raw_response'] ?? [];
                
                if (isset($responseData['errcode']) && $responseData['errcode'] !== 0) {
                    $errorCode = $responseData['errcode'];
                    $errorMessage = $responseData['errmsg'] ?? 'Unknown error';
                    return $this->errorResponse("TTLock Gateway ListLock API error: {$errorMessage} (Code: {$errorCode})", 400);
                }
                
                return $this->successResponse([
                    'raw_response' => $responseData,
                ], 'Locks by gateway retrieved successfully');
            } else {
                return $response;
            }

        } catch (Exception $e) {
            Log::error('TTLock Service: Get locks by gateway failed', [
                'gateway_id' => $gatewayId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->errorResponse('Failed to get locks by gateway: ' . $e->getMessage());
        }
    }

    /**
     * Get comprehensive gateway status using real TTLock API endpoints
     *
     * @return array
     */
    public function getGatewayStatus(): array
    {
        try {
            // Get gateway list first using the correct API endpoint
            $gatewayListResponse = $this->getGatewayList(1, 20, 0);
            
            if (!$gatewayListResponse['success']) {
                return $this->successResponse([
                    'total_gateways' => 0,
                    'online_gateways' => 0,
                    'offline_gateways' => 0,
                    'status' => 'offline',
                    'reason' => 'Cannot get gateway list: ' . $gatewayListResponse['message'],
                    'gateways' => [],
                    'last_check' => now()->toISOString()
                ], 'Gateway status retrieved successfully');
            }

            $gatewayData = $gatewayListResponse['data']['raw_response'] ?? [];
            $gateways = $gatewayData['list'] ?? [];
            $totalGateways = count($gateways);
            $onlineGateways = 0;
            $gatewayDetails = [];

            // Process each gateway from the API response
            foreach ($gateways as $gateway) {
                $gatewayId = $gateway['gatewayId'] ?? null;
                $gatewayName = $gateway['gatewayName'] ?? 'Unknown Gateway';
                $gatewayMac = $gateway['gatewayMac'] ?? 'Unknown';
                $isOnline = ($gateway['isOnline'] ?? 0) == 1;
                $lockNum = $gateway['lockNum'] ?? 0;
                $gatewayVersion = $gateway['gatewayVersion'] ?? 1;
                $networkName = $gateway['networkName'] ?? 'Unknown';
                
                if ($isOnline) {
                    $onlineGateways++;
                }
                
                // Get additional lock details for this gateway
                $lockDetails = [];
                if ($gatewayId && $lockNum > 0) {
                    $locksResponse = $this->getLocksByGateway($gatewayId, 1, $lockNum);
                    if ($locksResponse['success']) {
                        $locksData = $locksResponse['data']['raw_response'] ?? [];
                        $lockDetails = $locksData['list'] ?? [];
                    }
                }
                
                $gatewayDetails[] = [
                    'gateway_id' => $gatewayId,
                    'gateway_name' => $gatewayName,
                    'gateway_mac' => $gatewayMac,
                    'gateway_version' => $gatewayVersion,
                    'network_name' => $networkName,
                    'is_online' => $isOnline,
                    'status' => $isOnline ? 'online' : 'offline',
                    'reason' => $isOnline ? "Gateway online - {$lockNum} locks connected" : 'Gateway offline',
                    'lock_count' => $lockNum,
                    'locks' => $lockDetails,
                    'last_check' => now()->toISOString()
                ];
            }

            $overallStatus = $onlineGateways > 0 ? 'online' : 'offline';
            $overallReason = $totalGateways > 0 
                ? "{$onlineGateways}/{$totalGateways} gateways online" 
                : 'No gateways found';

            return $this->successResponse([
                'total_gateways' => $totalGateways,
                'online_gateways' => $onlineGateways,
                'offline_gateways' => $totalGateways - $onlineGateways,
                'status' => $overallStatus,
                'reason' => $overallReason,
                'gateways' => $gatewayDetails,
                'pagination' => [
                    'page_no' => $gatewayData['pageNo'] ?? 1,
                    'page_size' => $gatewayData['pageSize'] ?? 20,
                    'pages' => $gatewayData['pages'] ?? 1,
                    'total' => $gatewayData['total'] ?? $totalGateways
                ],
                'last_check' => now()->toISOString()
            ], 'Gateway status retrieved successfully');

        } catch (Exception $e) {
            Log::error('TTLock Service: Get gateway status failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->successResponse([
                'total_gateways' => 0,
                'online_gateways' => 0,
                'offline_gateways' => 0,
                'status' => 'offline',
                'reason' => 'Gateway status check failed: ' . $e->getMessage(),
                'gateways' => [],
                'last_check' => now()->toISOString()
            ], 'Gateway status retrieved successfully');
        }
    }

    /**
     * Get lock detail using TTLock API /v3/lock/detail
     *
     * @param string $lockId
     * @param string $accessToken
     * @return array
     */
    public function getLockDetail(string $lockId, string $accessToken): array
    {
        try {
            $date = round(microtime(true) * 1000);
            
            $data = [
                'clientId' => env('TTLOCK_CLIENT_ID'),
                'accessToken' => $accessToken,
                'lockId' => $lockId,
                'date' => $date,
            ];

            Log::info('TTLock Service: Getting lock detail', [
                'lock_id' => $lockId,
                'timestamp' => now(),
            ]);

            $response = $this->makeLockApiRequest('/v3/lock/detail', $data, 'POST');

            if ($response['success']) {
                $responseData = $response['data']['raw_response'] ?? [];
                
                if (isset($responseData['errcode']) && $responseData['errcode'] !== 0) {
                    $errorCode = $responseData['errcode'];
                    $errorMessage = $responseData['errmsg'] ?? 'Unknown error';
                    return $this->errorResponse("TTLock Lock Detail API error: {$errorMessage} (Code: {$errorCode})", 400);
                }
                
                return $this->successResponse($responseData, 'Lock detail retrieved successfully');
            } else {
                return $response;
            }

        } catch (Exception $e) {
            Log::error('TTLock Service: Get lock detail failed', [
                'lock_id' => $lockId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->errorResponse('Failed to get lock detail: ' . $e->getMessage());
        }
    }

    /**
     * Get comprehensive lock status with real API data
     *
     * @param string $lockId
     * @return array
     */
    public function getLockOnlineStatus(string $lockId): array
    {
        try {
            $tokenResponse = $this->getAccessToken();
            if (!$tokenResponse['success']) {
                return $this->successResponse([
                    'lock_id' => $lockId,
                    'is_online' => false,
                    'status' => 'offline',
                    'reason' => 'Cannot get access token - lock appears offline',
                    'last_seen' => null,
                    'battery_level' => null,
                    'is_locked' => null,
                    'lock_name' => 'Unknown'
                ], 'Lock status retrieved successfully');
            }

            $accessToken = $tokenResponse['data']['access_token'];

            // Get lock detail to check real status
            $lockDetailResponse = $this->getLockDetail($lockId, $accessToken);

            if ($lockDetailResponse['success']) {
                $lockData = $lockDetailResponse['data']['raw_response'] ?? [];
                
                // Determine if lock is online based on real data
                $isOnline = true;
                $reason = 'Lock is online and responsive';
                $batteryLevel = $lockData['electricQuantity'] ?? null;
                $isLocked = $lockData['isLocked'] ?? null;
                $lockName = $lockData['lockName'] ?? 'Unknown Lock';
                
                // Check battery level - if very low, consider it offline
                if ($batteryLevel !== null && $batteryLevel < 5) {
                    $isOnline = false;
                    $reason = 'Lock appears offline - battery critically low (' . $batteryLevel . '%)';
                } elseif ($batteryLevel !== null) {
                    $reason .= ' - Battery level: ' . $batteryLevel . '%';
                }
                
                // Check if lock is locked/unlocked (indicates connectivity)
                if ($isLocked !== null) {
                    $reason .= ' - Status: ' . ($isLocked ? 'Locked' : 'Unlocked');
                }
                
                return $this->successResponse([
                    'lock_id' => $lockId,
                    'is_online' => $isOnline,
                    'status' => $isOnline ? 'online' : 'offline',
                    'reason' => $reason,
                    'last_seen' => now()->toISOString(),
                    'battery_level' => $batteryLevel,
                    'is_locked' => $isLocked,
                    'lock_name' => $lockName,
                    'lock_alias' => $lockData['lockAlias'] ?? 'Unknown'
                ], 'Lock status retrieved successfully');
            } else {
                // Check if it's a connectivity issue
                $errorMessage = $lockDetailResponse['message'] ?? '';
                if (strpos($errorMessage, 'timeout') !== false || 
                    strpos($errorMessage, 'connection') !== false ||
                    strpos($errorMessage, 'network') !== false ||
                    strpos($errorMessage, 'refused') !== false) {
                    return $this->successResponse([
                        'lock_id' => $lockId,
                        'is_online' => false,
                        'status' => 'offline',
                        'reason' => 'Lock appears offline - network connectivity issue',
                        'last_seen' => null,
                        'battery_level' => null,
                        'is_locked' => null,
                        'lock_name' => 'Unknown'
                    ], 'Lock status retrieved successfully');
                }
                
                // API error but network is working, consider lock online
                return $this->successResponse([
                    'lock_id' => $lockId,
                    'is_online' => true,
                    'status' => 'online',
                    'reason' => 'Lock appears online - API accessible but detail failed: ' . $errorMessage,
                    'last_seen' => now()->toISOString(),
                    'battery_level' => null,
                    'is_locked' => null,
                    'lock_name' => 'Unknown'
                ], 'Lock status retrieved successfully');
            }

        } catch (Exception $e) {
            Log::error('TTLock Service: Get lock online status failed', [
                'lock_id' => $lockId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->successResponse([
                'lock_id' => $lockId,
                'is_online' => false,
                'status' => 'offline',
                'reason' => 'Lock appears offline due to error: ' . $e->getMessage(),
                'last_seen' => null,
                'battery_level' => null,
                'is_locked' => null,
                'lock_name' => 'Unknown'
            ], 'Lock status retrieved successfully');
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

                if ($method === 'GET') {
                    $response = Http::timeout(30)
                        ->retry(1, 1000)
                        ->get($url, $data);
                } else {
                    $response = Http::asForm()
                        ->timeout(30)
                        ->retry(1, 1000)
                        ->$method($url, $data);
                }

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
