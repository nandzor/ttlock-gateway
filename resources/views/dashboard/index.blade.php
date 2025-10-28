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
              <p class="text-blue-100">Here's what's happening with your HPS system today.</p>
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

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Users -->
      <x-stat-card title="Total Users" :value="$totalUsers" color="blue" :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z\'/>'" />

      <!-- HPS Emas -->
      <x-stat-card title="HPS Emas Items" :value="$totalHpsEmas" color="yellow" :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z\'/>'" />

      <!-- HPS Elektronik -->
      <x-stat-card title="HPS Elektronik Items" :value="$totalHpsElektronik" color="green" :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z\'/>'" />

      <!-- FAQ -->
      <x-stat-card title="FAQ Items" :value="$totalFaq" color="purple" :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z\'/>'" />
    </div>

    <!-- HPS Value Statistics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <!-- HPS Emas Value -->
      <x-card title="HPS Emas Value Statistics">
        <div class="space-y-4">
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Total Value:</span>
            <span class="text-2xl font-bold text-yellow-600">{{ format_currency_id($totalHpsEmasValue) }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Average Value:</span>
            <span class="text-xl font-semibold text-yellow-500">{{ format_currency_id($avgHpsEmasValue) }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Total Items:</span>
            <span class="text-xl font-semibold text-gray-700">{{ $totalHpsEmas }}</span>
          </div>
        </div>
      </x-card>

      <!-- HPS Elektronik Value -->
      <x-card title="HPS Elektronik Value Statistics">
        <div class="space-y-4">
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Total Value:</span>
            <span class="text-2xl font-bold text-green-600">{{ format_currency_id($totalHpsElektronikValue) }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Average Value:</span>
            <span class="text-xl font-semibold text-green-500">{{ format_currency_id($avgHpsElektronikValue) }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Active Items:</span>
            <span class="text-xl font-semibold text-green-600">{{ $activeHpsElektronik }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Total Items:</span>
            <span class="text-xl font-semibold text-gray-700">{{ $totalHpsElektronik }}</span>
          </div>
        </div>
      </x-card>
    </div>

    <!-- Recent Data Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <!-- Recent HPS Emas -->
      <x-card title="Recent HPS Emas" :padding="false">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis Barang</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              @forelse($recentHpsEmas as $item)
                <tr class="hover:bg-gray-50">
                  <td class="px-4 py-3 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                      {{ Str::limit($item->jenis_barang, 20) }}
                    </div>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                    Rp {{ number_format($item->nilai_taksiran_rp, 0, ',', '.') }}
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="2" class="px-4 py-8 text-center text-gray-400">
                    No HPS Emas data
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($recentHpsEmas->count() > 0)
          <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
            <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
              View all HPS Emas →
            </a>
          </div>
        @endif
      </x-card>

      <!-- Recent HPS Elektronik -->
      <x-card title="Recent HPS Elektronik" :padding="false">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              @forelse($recentHpsElektronik as $item)
                <tr class="hover:bg-gray-50">
                  <td class="px-4 py-3 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                      {{ Str::limit($item->barang, 15) }}
                    </div>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                    Rp {{ number_format($item->harga, 0, ',', '.') }}
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <x-badge :variant="$item->active ? 'success' : 'danger'">
                      {{ $item->active ? 'Aktif' : 'Tidak Aktif' }}
                    </x-badge>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="px-4 py-8 text-center text-gray-400">
                    No HPS Elektronik data
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($recentHpsElektronik->count() > 0)
          <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
            <a href="{{ route('hps-elektronik.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
              View all HPS Elektronik →
            </a>
          </div>
        @endif
      </x-card>

      <!-- Recent FAQ -->
      <x-card title="Recent FAQ" :padding="false">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Question</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              @forelse($recentFaq as $item)
                <tr class="hover:bg-gray-50">
                  <td class="px-4 py-3">
                    <div class="text-sm text-gray-900">
                      {{ Str::limit($item->question, 30) }}
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td class="px-4 py-8 text-center text-gray-400">
                    No FAQ data
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($recentFaq->count() > 0)
          <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
            <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
              View all FAQ →
            </a>
          </div>
        @endif
      </x-card>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8">
      <x-card title="Quick Actions">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <a href="{{ route('users.index') }}"
            class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors">
            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Manage Users</span>
          </a>

          <a href="#"
            class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-yellow-500 hover:bg-yellow-50 transition-colors">
            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">HPS Emas</span>
          </a>

          <a href="{{ route('hps-elektronik.index') }}"
            class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors">
            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">HPS Elektronik</span>
          </a>

          <a href="#"
            class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-colors">
            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">FAQ Chatbot</span>
          </a>
        </div>
      </x-card>
    </div>
  </div>
@endsection
