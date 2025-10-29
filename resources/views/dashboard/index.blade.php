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

    <!-- TTLock Status Indicators -->
    <div class="mb-8">
      <h2 class="text-xl font-semibold text-gray-900 mb-4">TTLock System Status</h2>
      
      <!-- Gateway Status -->
      <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Gateway Status</h3>
          <div class="flex items-center space-x-2">
            @if($gatewayStatus['success'] && $gatewayStatus['data']['status'] === 'online')
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                <span class="w-2 h-2 bg-green-500 rounded-full mr-1 animate-pulse"></span>
                Online
              </span>
            @else
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                <span class="w-2 h-2 bg-red-500 rounded-full mr-1"></span>
                Offline
              </span>
            @endif
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="text-center">
            <div class="text-2xl font-bold text-gray-900">{{ $gatewayStatus['success'] ? $gatewayStatus['data']['total_gateways'] : 0 }}</div>
            <div class="text-sm text-gray-500">Total Gateways</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-green-600">{{ $gatewayStatus['success'] ? $gatewayStatus['data']['online_gateways'] : 0 }}</div>
            <div class="text-sm text-gray-500">Online</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-red-600">{{ $gatewayStatus['success'] ? $gatewayStatus['data']['offline_gateways'] : 0 }}</div>
            <div class="text-sm text-gray-500">Offline</div>
          </div>
        </div>
        
        @if($gatewayStatus['success'])
          <div class="mt-4 p-3 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-600">
              <strong>Status:</strong> {{ $gatewayStatus['data']['reason'] }}
            </p>
            <p class="text-xs text-gray-500 mt-1">
              Last checked: {{ \Carbon\Carbon::parse($gatewayStatus['data']['last_check'])->diffForHumans() }}
            </p>
          </div>
          
          @if(isset($gatewayStatus['data']['gateways']) && count($gatewayStatus['data']['gateways']) > 0)
            <div class="mt-4">
              <h4 class="text-sm font-medium text-gray-900 mb-2">Gateway Details:</h4>
              <div class="space-y-2">
                @foreach($gatewayStatus['data']['gateways'] as $gateway)
                  <div class="p-3 bg-white rounded-lg border border-gray-200">
                    <div class="flex items-center justify-between">
                      <div>
                        <h5 class="font-medium text-gray-900">{{ $gateway['gateway_name'] }}</h5>
                        <p class="text-xs text-gray-500">ID: {{ $gateway['gateway_id'] }} | MAC: {{ $gateway['gateway_mac'] }}</p>
                      </div>
                      <div class="text-right">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $gateway['is_online'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                          {{ $gateway['status'] }}
                        </span>
                        <p class="text-xs text-gray-500 mt-1">{{ $gateway['lock_count'] }} locks</p>
                      </div>
                    </div>
                    <div class="mt-2 text-xs text-gray-600">
                      <p>Version: {{ $gateway['gateway_version'] }} | Network: {{ $gateway['network_name'] }}</p>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          @else
            <div class="mt-4 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
              <p class="text-sm text-yellow-800">
                <strong>No gateways found.</strong> Please ensure your TTLock gateways are properly configured and connected to your account.
              </p>
            </div>
          @endif
        @endif
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

    <div class="mt-6">
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

      <x-card title="Today Overview">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <div class="text-sm text-gray-500">Callbacks (24h)</div>
            <div class="text-2xl font-bold">{{ $recentCallbacks }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-500">Processed</div>
            <div class="text-2xl font-bold text-green-600">{{ $processedCallbacks }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-500">Unprocessed</div>
            <div class="text-2xl font-bold text-yellow-600">{{ $unprocessedCallbacks }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-500">Total</div>
            <div class="text-2xl font-bold text-indigo-600">{{ $totalCallbacks }}</div>
          </div>
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
