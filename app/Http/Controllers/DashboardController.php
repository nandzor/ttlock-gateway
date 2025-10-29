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
        
        // Get locks for the first gateway (default selected)
        $selectedGatewayId = null;
        $selectedGateway = null;
        $gatewayLocks = [];
        
        if ($gatewayStatus['success'] && isset($gatewayStatus['data']['gateways']) && count($gatewayStatus['data']['gateways']) > 0) {
            $selectedGateway = $gatewayStatus['data']['gateways'][0];
            $selectedGatewayId = $selectedGateway['gateway_id'] ?? null;
            
            if ($selectedGatewayId) {
                $locksResponse = $this->ttlockService->getLocksByGateway($selectedGatewayId, 1, 100);
                if ($locksResponse['success']) {
                    $locksData = $locksResponse['data']['raw_response'] ?? [];
                    $gatewayLocks = $locksData['list'] ?? [];
                }
            }
        }

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

        // Username pie chart data - aggregate by username
        $usernameStats = TTLockCallbackHistory::selectRaw('username, COUNT(*) as count')
            ->whereNotNull('username')
            ->where('username', '!=', '')
            ->groupBy('username')
            ->orderByDesc('count')
            ->limit(10) // Top 10 usernames
            ->get();

        $usernameLabels = $usernameStats->pluck('username')->map(function($username) {
            return $username ?: 'Unknown';
        })->toArray();
        
        $usernameCounts = $usernameStats->pluck('count')->toArray();
        
        // Generate colors for pie chart
        $colors = [
            '#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
            '#06B6D4', '#F97316', '#EC4899', '#84CC16', '#6366F1'
        ];
        $backgroundColor = array_slice($colors, 0, count($usernameLabels));
        
        // Add "Others" if there are more than 10 usernames
        $totalUsernames = TTLockCallbackHistory::whereNotNull('username')
            ->where('username', '!=', '')
            ->distinct('username')
            ->count('username');
        
        if ($totalUsernames > 10) {
            $top10Count = array_sum($usernameCounts);
            $allCount = TTLockCallbackHistory::whereNotNull('username')
                ->where('username', '!=', '')
                ->count();
            $othersCount = $allCount - $top10Count;
            
            if ($othersCount > 0) {
                $usernameLabels[] = 'Others';
                $usernameCounts[] = $othersCount;
                $backgroundColor[] = '#94A3B8';
            }
        }

        // Default to empty data if no usernames found
        if (empty($usernameLabels)) {
            $usernameLabels = ['No Data'];
            $usernameCounts = [0];
            $backgroundColor = ['#94A3B8'];
        }

        $chartUsernamePie = [
            'labels' => $usernameLabels,
            'datasets' => [
                [
                    'label' => 'Callbacks by Username',
                    'data' => $usernameCounts,
                    'backgroundColor' => $backgroundColor,
                    'borderWidth' => 2,
                    'borderColor' => '#FFFFFF',
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
            'chartUsernamePie',
            'gatewayStatus',
            'selectedGateway',
            'selectedGatewayId',
            'gatewayLocks'
        ));
    }
}
