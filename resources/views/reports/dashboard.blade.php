@extends('layouts.app')

@section('title', 'Reports Dashboard')
@section('page-title', 'Reports Dashboard')

@section('content')
  <!-- Page Header with Export -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
      <h2 class="text-2xl font-bold text-gray-900">Analytics Dashboard</h2>
      <p class="text-sm text-gray-600 mt-1">
        Period: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} -
        {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
      </p>
    </div>

    @if ($totalDetections > 0)
      <div class="mt-4 sm:mt-0">
        <x-dropdown align="right" width="48">
          <x-slot name="trigger">
            <button type="button"
              class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500 transition-all shadow-sm">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
              </svg>
              Export Report
              <svg class="w-4 h-4 ml-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                  clip-rule="evenodd" />
              </svg>
            </button>
          </x-slot>

          <x-dropdown-link :href="route(
              'reports.dashboard.export',
              array_merge(request()->only(['date_from', 'date_to', 'branch_id']), [
                  'format' => 'excel',
              ]),
          )" variant="success">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
            </svg>
            Export to Excel
          </x-dropdown-link>

          <x-dropdown-link :href="route(
              'reports.dashboard.export',
              array_merge(request()->only(['date_from', 'date_to', 'branch_id']), [
                  'format' => 'pdf',
              ]),
          )" variant="danger">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            Export to PDF
          </x-dropdown-link>
        </x-dropdown>
      </div>
    @endif
  </div>

  <!-- Statistics -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <x-stat-card title="Total Detections" :value="$totalDetections" icon="eye" color="blue" />
    <x-stat-card title="Unique Persons" :value="$uniquePersons" icon="users" color="green" />
    <x-stat-card title="Active Branches" :value="$uniqueBranches" icon="building" color="purple" />
    <x-stat-card title="Active Devices" :value="$uniqueDevices" icon="camera" color="orange" />
  </div>

  <!-- Filters -->
  <x-card class="mb-6">
    <div class="p-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Report Filters</h3>
      <form method="GET">
        <div class="flex flex-col lg:flex-row lg:items-end gap-4">
          <!-- Filter Fields -->
          <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-input type="date" name="date_from" :value="$dateFrom" label="From Date" />
            <x-input type="date" name="date_to" :value="$dateTo" label="To Date" />
            <x-company-branch-select name="branch_id" :value="$branchId" label="Select Branch"
              placeholder="All Branches" />
          </div>

          <!-- Action Button -->
          <x-button type="submit" variant="primary" size="md" class="w-full md:w-auto">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Apply Filters
          </x-button>
        </div>
      </form>
    </div>
  </x-card>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Daily Trend Chart -->
    <x-detection-trend-chart :data="$dailyTrend" title="Detection Trend" />

    <!-- Top Branches -->
    <x-card>
      <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Top Branches by Detections</h3>
        <div class="space-y-4">
          @foreach ($topBranches as $item)
            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
              <div>
                <p class="font-semibold text-gray-900">{{ $item->branch->branch_name ?? 'N/A' }}</p>
                <p class="text-xs text-gray-500">{{ $item->branch->city_name ?? '' }}</p>
              </div>
              <x-badge variant="primary" size="lg">
                {{ number_format($item->detection_count) }}
              </x-badge>
            </div>
          @endforeach
        </div>
      </div>
    </x-card>
  </div>
@endsection
