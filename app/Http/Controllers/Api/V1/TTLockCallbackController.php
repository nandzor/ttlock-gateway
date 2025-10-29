<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * TTLock Callback Controller
 *
 * Handles callbacks from TTLock platform (https://euopen.ttlock.com/)
 * Processes various lock-related events and operations
 */
class TTLockCallbackController extends BaseController
{
    /**
     * Handle TTLock callback requests
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function callback(Request $request)
    {
        try {
            // Log incoming callback for debugging
            Log::info('TTLock Callback Received', [
                'data' => $request->all(),
                'headers' => $request->headers->all(),
                'timestamp' => now(),
            ]);

            // // Validate required fields based on TTLock API documentation
            // $validator = Validator::make($request->all(), [
            //     'lockId' => 'required|string',
            //     'type' => 'required|integer',
            //     'timestamp' => 'required|integer',
            //     'lockmac' => 'required|string',
            //     'data' => 'required|string',
            // ]);

            // if ($validator->fails()) {
            //     Log::warning('TTLock Callback Validation Failed', [
            //         'errors' => $validator->errors(),
            //         'request_data' => $request->all(),
            //     ]);

            //     return $this->validationErrorResponse(
            //         $validator->errors(),
            //         'Invalid callback data'
            //     );
            // }

            // // Extract callback data
            // $lockId = $request->input('lockId');
            // $type = $request->input('type');
            // $timestamp = $request->input('timestamp');
            // $lockMac = $request->input('lockmac');
            // $data = $request->input('data');

            // // Process different callback types
            // $result = $this->processCallback($lockId, $type, $timestamp, $lockMac, $data);

            // Log::info('TTLock Callback Processed Successfully', [
            //     'lockId' => $lockId,
            //     'type' => $type,
            //     'result' => $result,
            // ]);

            return $this->successResponse($request->all(), 'Callback processed successfully');

        } catch (\Exception $e) {
            Log::error('TTLock Callback Processing Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return $this->serverErrorResponse('Failed to process callback');
        }
    }

    /**
     * Process different types of callbacks
     *
     * @param string $lockId
     * @param int $type
     * @param int $timestamp
     * @param string $lockMac
     * @param string $data
     * @return array
     */
    private function processCallback(string $lockId, int $type, int $timestamp, string $lockMac, string $data): array
    {
        $processedData = [
            'lock_id' => $lockId,
            'type' => $type,
            'timestamp' => $timestamp,
            'lock_mac' => $lockMac,
            'processed_at' => now()->toISOString(),
        ];

        // Process based on callback type
        switch ($type) {
            case 1: // Lock operation (unlock/lock)
                $processedData['event'] = 'lock_operation';
                $processedData['raw_data'] = json_decode($data, true);
                $processedData['message'] = 'Lock operation received';
                break;

            case 2: // Passcode operation
                $processedData['event'] = 'passcode_operation';
                $processedData['raw_data'] = json_decode($data, true);
                $processedData['message'] = 'Passcode operation received';
                break;

            case 3: // Card operation
                $processedData['event'] = 'card_operation';
                $processedData['raw_data'] = json_decode($data, true);
                $processedData['message'] = 'Card operation received';
                break;

            case 4: // Fingerprint operation
                $processedData['event'] = 'fingerprint_operation';
                $processedData['raw_data'] = json_decode($data, true);
                $processedData['message'] = 'Fingerprint operation received';
                break;

            case 5: // Remote unlock
                $processedData['event'] = 'remote_unlock';
                $processedData['raw_data'] = json_decode($data, true);
                $processedData['message'] = 'Remote unlock operation received';
                break;

            case 6: // Gateway offline
                $processedData['event'] = 'gateway_offline';
                $processedData['message'] = 'Gateway is offline';
                break;

            case 7: // Gateway online
                $processedData['event'] = 'gateway_online';
                $processedData['message'] = 'Gateway is online';
                break;

            case 8: // Lock battery low
                $processedData['event'] = 'battery_low';
                $processedData['message'] = 'Lock battery is low';
                break;

            case 9: // Lock tamper alarm
                $processedData['event'] = 'tamper_alarm';
                $processedData['message'] = 'Lock tamper alarm triggered';
                break;

            default:
                $processedData['event'] = 'unknown';
                $processedData['raw_data'] = json_decode($data, true);
                $processedData['message'] = 'Unknown callback type';
                break;
        }

        // Here you can add your business logic:
        // - Update database records
        // - Send notifications
        // - Trigger other processes
        // - etc.

        return $processedData;
    }

    /**
     * Get supported callback types
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCallbackTypes()
    {
        $callbackTypes = [
            1 => 'Lock Operation (Unlock/Lock)',
            2 => 'Passcode Operation',
            3 => 'Card Operation',
            4 => 'Fingerprint Operation',
            5 => 'Remote Unlock',
            6 => 'Gateway Offline',
            7 => 'Gateway Online',
            8 => 'Lock Battery Low',
            9 => 'Lock Tamper Alarm',
        ];

        return $this->successResponse($callbackTypes, 'Supported callback types retrieved successfully');
    }
}
