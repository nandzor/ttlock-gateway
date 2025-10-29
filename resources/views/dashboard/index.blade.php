@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
  <div class="max-w-7xl mx-auto">
    <!-- Welcome Banner -->
    <div class="mb-8">
      <div
        class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 rounded-2xl shadow-2xl">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -mr-32 -mt-32"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-white opacity-5 rounded-full -ml-20 -mb-20"></div>

        <div class="relative p-8">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold text-white mb-2">Welcome back, {{ auth()->user()->name }}! 👋</h1>
              <p class="text-blue-100">Here's what's happening with your system today.</p>
            </div>
            <div class="hidden md:block">
              <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-xl p-4">
                <p class="text-100 text-sm">{{ now()->format('l, F j, Y') }}</p>
                <p class="text text-2xl font-bold">{{ now()->format('H:i') }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- TTLock Gateway and Lock Management -->
    <div class="mb-8">
      <h2 class="text-xl font-semibold text-gray-900 mb-4">TTLock Gateway & Lock Management</h2>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Card 1: Gateway Status -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
          <!-- Header -->
          <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900">Gateway Status</h3>
              @php
                $isOnline = $gatewayStatus['success'] && ($gatewayStatus['data']['status'] ?? 'offline') === 'online';
                $gatewayStats = $gatewayStatus['success'] ? $gatewayStatus['data'] : [];
                $totalGateways = $gatewayStats['total_gateways'] ?? 0;
                $onlineGateways = $gatewayStats['online_gateways'] ?? 0;
                $offlineGateways = $gatewayStats['offline_gateways'] ?? 0;
              @endphp
              <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                  <div class="flex items-center gap-1.5">
                    <span class="text-xs text-gray-500">Total:</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $totalGateways }}</span>
                  </div>
                  <span class="text-gray-300">•</span>
                  <div class="flex items-center gap-1.5">
                    <span class="text-xs text-gray-500">Online:</span>
                    <span class="text-sm font-semibold text-green-600">{{ $onlineGateways }}</span>
                  </div>
                  <span class="text-gray-300">•</span>
                  <div class="flex items-center gap-1.5">
                    <span class="text-xs text-gray-500">Offline:</span>
                    <span class="text-sm font-semibold text-red-600">{{ $offlineGateways }}</span>
                  </div>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $isOnline ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                  <span class="w-2 h-2 {{ $isOnline ? 'bg-green-500' : 'bg-red-500' }} rounded-full mr-1.5 {{ $isOnline ? 'animate-pulse' : '' }}"></span>
                  {{ $isOnline ? 'Online' : 'Offline' }}
                </span>
              </div>
            </div>
          </div>
          
          <!-- Gateway List -->
          @if($gatewayStatus['success'] && isset($gatewayStatus['data']['gateways']) && count($gatewayStatus['data']['gateways']) > 0)
            <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
              @foreach($gatewayStatus['data']['gateways'] as $index => $gateway)
                <div 
                  class="px-4 py-3 cursor-pointer transition-all gateway-item {{ $selectedGatewayId == $gateway['gateway_id'] ? 'bg-blue-50 border-l-4 border-blue-500' : 'hover:bg-gray-50' }}"
                  data-gateway-id="{{ $gateway['gateway_id'] }}"
                  onclick="selectGateway({{ $gateway['gateway_id'] }}, '{{ addslashes($gateway['gateway_name']) }}')"
                >
                  <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                      <!-- Baris 1: Gateway Name -->
                      <div class="flex items-center gap-2 mb-1.5">
                        <h5 class="font-semibold text-sm text-gray-900 truncate">{{ $gateway['gateway_name'] }}</h5>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $gateway['is_online'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                          <span class="w-1.5 h-1.5 {{ $gateway['is_online'] ? 'bg-green-500' : 'bg-red-500' }} rounded-full mr-1"></span>
                          {{ $gateway['status'] }}
                        </span>
                      </div>
                      
                      <!-- Baris 2: Details -->
                      <div class="flex items-center gap-2 text-xs text-gray-500">
                        <span class="font-mono text-gray-600">{{ $gateway['gateway_id'] }}</span>
                        <span class="text-gray-300">•</span>
                        <span class="font-mono">{{ $gateway['gateway_mac'] }}</span>
                        <span class="text-gray-300">•</span>
                        <span>v{{ $gateway['gateway_version'] }}</span>
                        <span class="text-gray-300">•</span>
                        <span class="flex items-center gap-1 truncate">
                          <svg class="w-3 h-3 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                          </svg>
                          <span class="truncate">{{ $gateway['network_name'] }}</span>
                        </span>
                      </div>
                    </div>
                    <div class="flex-shrink-0 ml-3">
                      <div class="text-right">
                        <div class="inline-flex items-center px-2 py-1 rounded-md bg-gray-100 text-gray-700 text-xs font-medium">
                          {{ $gateway['lock_count'] }} {{ $gateway['lock_count'] == 1 ? 'lock' : 'locks' }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="p-6 text-center">
              <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-yellow-100 mb-3">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
              </div>
              <p class="text-sm font-medium text-gray-900 mb-1">No gateways found</p>
              <p class="text-xs text-gray-500">Ensure your TTLock gateways are properly configured and connected.</p>
            </div>
          @endif
        </div>

        <!-- Card 2: Lock List -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
          <!-- Header -->
          <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900">
                Lock List
                @if($selectedGateway)
                  <span class="text-sm font-normal text-gray-500 ml-2">- {{ $selectedGateway['gateway_name'] }}</span>
                @endif
              </h3>
              <div id="lock-count-badge" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                {{ count($gatewayLocks) }} locks
              </div>
            </div>
          </div>
          
          <div id="locks-container" class="max-h-96 overflow-y-auto divide-y divide-gray-100">
            @if($selectedGatewayId && count($gatewayLocks) > 0)
              @foreach($gatewayLocks as $lock)
                @include('dashboard.partials.lock-item', ['lock' => $lock])
              @endforeach
            @elseif($selectedGatewayId && count($gatewayLocks) == 0)
              <div class="p-6 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 mb-3">
                  <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                  </svg>
                </div>
                <p class="text-sm font-medium text-gray-900 mb-1">No locks found</p>
                <p class="text-xs text-gray-500">This gateway has no locks connected.</p>
              </div>
            @else
              <div class="p-6 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-3">
                  <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                  </svg>
                </div>
                <p class="text-sm font-medium text-gray-900 mb-1">Select a gateway</p>
                <p class="text-xs text-gray-500">Click on a gateway to view its locks.</p>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Users -->
      <x-stat-card title="Total Users" :value="$totalUsers" color="blue" />
      <x-stat-card title="Total Callbacks" :value="$totalCallbacks" color="indigo" />
      <x-stat-card title="Processed" :value="$processedCallbacks" color="green" />
      <x-stat-card title="Unprocessed" :value="$unprocessedCallbacks" color="yellow" />
    </div>

    <div class="mt-6 mb-8">
      <x-card title="TTLock Callbacks - Last 7 Days">
        <div class="w-full">
          <canvas id="ttlockLast7Chart" height="350"></canvas>
        </div>
      </x-card>
    </div>



    <!-- Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <x-card title="Recent TTLock Events">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead>
              <tr>
                <th class="px-4 py-2 text-left">Date</th>
                <th class="px-4 py-2 text-left">Lock</th>
                <th class="px-4 py-2 text-left">Event</th>
                <th class="px-4 py-2 text-left">Battery</th>
                <th class="px-4 py-2 text-left">User</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @forelse($recentEvents as $h)
                <tr>
                  <td class="px-4 py-2 text-sm text-gray-700">{{ $h->created_at->format('Y-m-d H:i:s') }}</td>
                  <td class="px-4 py-2 text-sm">
                    <div class="font-semibold">{{ $h->lock_id }}</div>
                    <div class="text-gray-500">{{ $h->lock_mac }}</div>
                  </td>
                  <td class="px-4 py-2 text-sm">
                    <div class="font-semibold">{{ $h->event_type_description }}</div>
                    <div class="text-gray-500">{{ $h->message }}</div>
                  </td>
                  <td class="px-4 py-2 text-sm">{{ $h->electric_quantity }}% ({{ $h->battery_level_description }})</td>
                  <td class="px-4 py-2 text-sm">{{ $h->username ?? '-' }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="px-4 py-6 text-center text-gray-500">No recent events.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="mt-4">
          <a href="{{ route('ttlock.callback.histories.index') }}" class="btn">View all histories</a>
        </div>
      </x-card>

      <x-card title="Today Overview - Callbacks by Username">
        <div class="w-full">
          <canvas id="usernamePieChart" height="250"></canvas>
        </div>
      </x-card>
    </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  (function() {
    const ctx = document.getElementById('ttlockLast7Chart');
    if (!ctx) return;

    const data = {
      labels: @json($chartLast7Days['labels'] ?? []),
      datasets: @json($chartLast7Days['datasets'] ?? [])
    };

    const chart = new Chart(ctx, {
      type: 'line',
      data,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: { title: { display: true, text: 'Date' } },
          y: { beginAtZero: true, precision: 0, title: { display: true, text: 'Callbacks' } }
        },
        plugins: {
          legend: { display: true },
          tooltip: { mode: 'index', intersect: false }
        },
        elements: { point: { radius: 3 } }
      }
    });
  })();

  // Username Pie Chart
  (function() {
    const ctx = document.getElementById('usernamePieChart');
    if (!ctx) return;

    const data = {
      labels: @json($chartUsernamePie['labels'] ?? []),
      datasets: @json($chartUsernamePie['datasets'] ?? [])
    };

    // Check if there's actual data (not just "No Data")
    const hasData = data.labels.length > 0 && 
                    data.labels[0] !== 'No Data' && 
                    data.datasets[0].data.reduce((a, b) => a + b, 0) > 0;

    const chart = new Chart(ctx, {
      type: 'pie',
      data,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: hasData,
            position: 'right',
            labels: {
              padding: 15,
              usePointStyle: true,
              font: {
                size: 11
              }
            }
          },
          tooltip: {
            enabled: hasData,
            callbacks: {
              label: function(context) {
                const label = context.label || '';
                const value = context.parsed || 0;
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                if (total === 0) return label;
                const percentage = ((value / total) * 100).toFixed(1);
                return `${label}: ${value} (${percentage}%)`;
              }
            }
          }
        }
      }
    });

    // Show message if no data
    if (!hasData) {
      const noDataText = ctx.parentElement;
      if (noDataText) {
        noDataText.innerHTML = '<div class="p-8 text-center text-gray-500"><p>No username data available</p></div>';
      }
    }
  })();

  // Gateway and Lock Management - Helper Functions
  const LockHelpers = {
    /**
     * Calculate RSSI status and color
     * @param {number|null} rssi - RSSI value in dBm
     * @returns {Object} {status: string, color: string}
     */
    calculateRssiStatus(rssi) {
      if (rssi === null || rssi === undefined) {
        return { status: 'unknown', color: 'gray' };
      }
      
      if (rssi > -75) {
        return { status: 'strong', color: 'green' };
      } else if (rssi >= -85 && rssi <= -75) {
        return { status: 'medium', color: 'yellow' };
      } else {
        return { status: 'weak', color: 'red' };
      }
    },

    /**
     * Format update date to relative time
     * @param {number} timestamp - Timestamp in milliseconds
     * @returns {string} Formatted time string
     */
    formatUpdateDate(timestamp) {
      if (!timestamp) return '';
      
      const updateDate = new Date(timestamp);
      const now = new Date();
      const diffMs = now - updateDate;
      const diffMins = Math.floor(diffMs / 60000);
      const diffHours = Math.floor(diffMs / 3600000);
      const diffDays = Math.floor(diffMs / 86400000);
      
      if (diffMins < 1) return 'just now';
      if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
      if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
      return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
    },

    /**
     * Render lock item HTML
     * @param {Object} lock - Lock data object
     * @returns {string} HTML string
     */
    renderLockItem(lock) {
      const hasAlias = lock.lockAlias && lock.lockAlias !== lock.lockName;
      const rssiData = this.calculateRssiStatus(lock.rssi);
      const updateDateStr = this.formatUpdateDate(lock.updateDate);
      
      const signalHtml = lock.rssi !== null && lock.rssi !== undefined 
        ? `<span class="text-gray-300">•</span><span class="font-medium text-${rssiData.color}-600">${lock.rssi} dBm</span>`
        : '';
      
      const updateHtml = updateDateStr 
        ? `<span class="text-gray-300">•</span><span class="text-gray-400">${updateDateStr}</span>`
        : '';
      
      return `
        <div class="px-3 py-2 bg-white border-b border-gray-100 hover:bg-gray-50 transition-colors last:border-b-0">
          <div class="flex items-center justify-between gap-3">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1">
                ${hasAlias ? `
                  <h5 class="font-semibold text-sm text-gray-900 truncate">${lock.lockAlias}</h5>
                  <span class="text-xs text-gray-400 italic flex-shrink-0">${lock.lockName || 'Unknown'}</span>
                ` : `
                  <h5 class="font-semibold text-sm text-gray-900 truncate">${lock.lockName || 'Unknown Lock'}</h5>
                `}
              </div>
              
              <div class="flex items-center gap-2 text-xs text-gray-500">
                <span class="font-mono text-gray-600">${lock.lockId || 'Unknown'}</span>
                <span class="text-gray-300">•</span>
                <span class="font-mono">${lock.lockMac || 'Unknown'}</span>
                ${signalHtml}
                ${updateHtml}
              </div>
            </div>
          </div>
        </div>
      `;
    },

    /**
     * Update lock count badge
     * @param {number} count - Number of locks
     */
    updateLockCountBadge(count) {
      const badge = document.getElementById('lock-count-badge');
      if (badge) {
        badge.textContent = `${count} locks`;
      }
    },

    /**
     * Render empty state message
     * @param {string} message - Message to display
     * @param {string} type - Type: 'empty' or 'error'
     * @returns {string} HTML string
     */
    renderEmptyState(message, type = 'empty') {
      const colorClass = type === 'error' ? 'text-red-500' : 'text-gray-500';
      return `<div class="p-4 text-center ${colorClass}"><p>${message}</p></div>`;
    }
  };

  /**
   * Update active gateway visual state
   */
  function updateActiveGateway(gatewayId) {
    document.querySelectorAll('.gateway-item').forEach(item => {
      const itemGatewayId = parseInt(item.getAttribute('data-gateway-id'));
      if (itemGatewayId === gatewayId) {
        item.classList.add('bg-blue-50', 'border-l-4', 'border-blue-500');
        item.classList.remove('hover:bg-gray-50');
      } else {
        item.classList.remove('bg-blue-50', 'border-l-4', 'border-blue-500');
        item.classList.add('hover:bg-gray-50');
      }
    });
  }

  /**
   * Update lock list header with gateway name
   */
  function updateLockListHeader(gatewayName) {
    const lockHeader = document.querySelector('#locks-container').parentElement.querySelector('h3');
    if (lockHeader) {
      lockHeader.innerHTML = `Lock List <span class="text-sm font-normal text-gray-500 ml-2">- ${gatewayName}</span>`;
    }
  }

  /**
   * Fetch and render locks for selected gateway
   */
  function fetchAndRenderLocks(gatewayId) {
    const locksContainer = document.getElementById('locks-container');
    locksContainer.innerHTML = LockHelpers.renderEmptyState('Loading locks...');

    fetch(`/api/v1/dashboard/gateway/${gatewayId}/locks`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
      if (data.success && data.data?.locks?.length > 0) {
        const locks = data.data.locks;
        const total = data.data.pagination?.total || locks.length;
        
        LockHelpers.updateLockCountBadge(total);
        locksContainer.className = 'max-h-96 overflow-y-auto divide-y divide-gray-100';
        locksContainer.innerHTML = locks.map(lock => LockHelpers.renderLockItem(lock)).join('');
      } else {
        locksContainer.className = 'max-h-96 overflow-y-auto';
        locksContainer.innerHTML = `
          <div class="p-6 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 mb-3">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
              </svg>
            </div>
            <p class="text-sm font-medium text-gray-900 mb-1">No locks found</p>
            <p class="text-xs text-gray-500">This gateway has no locks connected.</p>
          </div>
        `;
        LockHelpers.updateLockCountBadge(0);
      }
    })
    .catch(error => {
      console.error('Error fetching locks:', error);
      locksContainer.className = 'max-h-96 overflow-y-auto';
      locksContainer.innerHTML = `
        <div class="p-6 text-center">
          <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-red-100 mb-3">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <p class="text-sm font-medium text-gray-900 mb-1">Error loading locks</p>
          <p class="text-xs text-gray-500">Please try again.</p>
        </div>
      `;
      LockHelpers.updateLockCountBadge(0);
    });
  }

  /**
   * Select gateway and update UI
   */
  function selectGateway(gatewayId, gatewayName) {
    updateActiveGateway(gatewayId);
    updateLockListHeader(gatewayName);
    fetchAndRenderLocks(gatewayId);
  }
</script>
@endpush

    <!-- Quick Actions -->
    <div class="mt-8">
      <x-card title="Quick Actions">
        <div class="grid grid-cols-1 gap-4">
          <a href="{{ route('users.index') }}"
            class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors">
            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Manage Users</span>
          </a>
        </div>
      </x-card>
    </div>
  </div>
@endsection
