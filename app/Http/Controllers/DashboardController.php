<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Models\TTLockCallbackHistory;
use App\Services\TTLockService;

class DashboardController extends Controller {
    protected $userService;
    protected $ttlockService;

    public function __construct(UserService $userService, TTLockService $ttlockService) {
        $this->userService = $userService;
        $this->ttlockService = $ttlockService;
    }

    /**
     * Display dashboard with system statistics
     */
    public function index() {
        // User statistics
        $totalUsers = \App\Models\User::count();

        // TTLock callback statistics
        $totalCallbacks = TTLockCallbackHistory::count();
        $processedCallbacks = TTLockCallbackHistory::processed()->count();
        $unprocessedCallbacks = TTLockCallbackHistory::unprocessed()->count();
        $recentCallbacks = TTLockCallbackHistory::recent(24)->count();

        // Recent events
        $recentEvents = TTLockCallbackHistory::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // TTLock Gateway and Lock Status
        $gatewayStatus = $this->ttlockService->getGatewayStatus();
        $lockStatus = $this->ttlockService->getLockOnlineStatus(env('TTLOCK_LOCKID', '17974276'));

        // Last 7 days chart data
        $labels = [];
        $counts = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->startOfDay();
            $labels[] = $day->format('Y-m-d');
            $counts[] = TTLockCallbackHistory::whereBetween('created_at', [$day, (clone $day)->endOfDay()])->count();
        }
        $chartLast7Days = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Callbacks',
                    'data' => $counts,
                    'borderColor' => '#4F46E5',
                    'backgroundColor' => 'rgba(79,70,229,0.15)',
                    'tension' => 0.3,
                    'fill' => true,
                ]
            ]
        ];

        return view('dashboard.index', compact(
            'totalUsers',
            'totalCallbacks',
            'processedCallbacks',
            'unprocessedCallbacks',
            'recentCallbacks',
            'recentEvents',
            'chartLast7Days',
            'gatewayStatus',
            'lockStatus'
        ));
    }
}
