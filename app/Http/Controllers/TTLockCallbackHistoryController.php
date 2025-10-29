<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TTLockCallbackHistory;
use App\Services\BaseExportService;
use App\Services\TTLockCallbackHistoryService;

class TTLockCallbackHistoryController extends Controller
{
    protected BaseExportService $exportService;
    protected TTLockCallbackHistoryService $historyService;

    public function __construct(BaseExportService $exportService, TTLockCallbackHistoryService $historyService)
    {
        $this->exportService = $exportService;
        $this->historyService = $historyService;
    }

    public function index(Request $request)
    {
        $histories = $this->historyService->paginateFromRequest($request, [
            'lock_id', 'event_type', 'record_type', 'processed', 'date_from', 'date_to', 'username', 'lock_mac'
        ]);

        $eventTypes = [
            'lock_operation' => 'Lock Operation',
            'passcode_operation' => 'Passcode Operation',
            'card_operation' => 'Card Operation',
            'fingerprint_operation' => 'Fingerprint Operation',
            'remote_unlock' => 'Remote Unlock',
            'gateway_offline' => 'Gateway Offline',
            'gateway_online' => 'Gateway Online',
            'battery_low' => 'Battery Low',
            'tamper_alarm' => 'Tamper Alarm',
            'unknown' => 'Unknown',
        ];

        // Get unique usernames for filter dropdown
        $usernames = TTLockCallbackHistory::select('username')
            ->whereNotNull('username')
            ->where('username', '!=', '')
            ->distinct()
            ->orderBy('username')
            ->pluck('username')
            ->toArray();

        return view('ttlock-callback-history.index', compact('histories', 'eventTypes', 'usernames'));
    }

    public function export(Request $request, string $format)
    {
        $allowed = ['pdf', 'excel'];
        if (!in_array($format, $allowed, true)) {
            abort(404);
        }

        $data = [
            'histories' => $this->historyService->listForExport($request),
            'filters' => $request->all(),
        ];

        $fileName = $this->exportService->generateFileName('TTLock_Callback_Histories');

        if ($format === 'pdf') {
            return $this->exportService->export(
                'pdf',
                null,
                'ttlock-callback-history.pdf',
                $data,
                $fileName,
                ['orientation' => 'landscape']
            );
        }

        return $this->exportService->export(
            'excel',
            \App\Exports\TTLockCallbackHistoryExport::class,
            'ttlock-callback-history.excel',
            $data,
            $fileName
        );
    }
}


