@extends('layouts.app')

@section('title', 'TTLock Callback Histories')

@section('content')
<div class="max-w-7xl mx-auto">
  <div class="mb-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">TTLock Callback Histories</h1>
        <p class="text-sm text-gray-500">Monitor and export lock callback events</p>
      </div>
    </div>
  </div>

  <!-- Search and Actions Card -->
  <x-card class="mb-4">
    <div class="p-4">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-3 lg:space-y-0">
        <div class="flex-1 max-w-md">
          <form method="GET" action="{{ route('ttlock.callback.histories.index') }}" class="flex">
            <x-input name="search" :value="request('search') ?? ''" placeholder="Search lock id, mac, user..." class="rounded-r-none border-r-0" />
            @if (request()->has('per_page'))
              <input type="hidden" name="per_page" value="{{ request()->get('per_page') }}">
            @endif
            @if (request()->has('event_type'))
              <input type="hidden" name="event_type" value="{{ request()->get('event_type') }}">
            @endif
            @if (request()->has('record_type'))
              <input type="hidden" name="record_type" value="{{ request()->get('record_type') }}">
            @endif
            @if (request()->has('processed'))
              <input type="hidden" name="processed" value="{{ request()->get('processed') }}">
            @endif
            @if (request()->has('date_from'))
              <input type="hidden" name="date_from" value="{{ request()->get('date_from') }}">
            @endif
            @if (request()->has('date_to'))
              <input type="hidden" name="date_to" value="{{ request()->get('date_to') }}">
            @endif
            @if (request()->has('username'))
              <input type="hidden" name="username" value="{{ request()->get('username') }}">
            @endif
            <button type="submit"
              class="px-6 py-2 bg-gray-600 text-white rounded-r-lg hover:bg-gray-700 transition-colors">
              Search
            </button>
          </form>
        </div>

        <div class="flex items-center space-x-4">
          <!-- Export Dropdown -->
          <x-dropdown align="right" width="48">
            <x-slot name="trigger">
              <button type="button" class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <x-icon name="export" class="w-4 h-4 mr-2" />
                Export
                <x-icon name="chevron-down" class="w-4 h-4 ml-2" />
              </button>
            </x-slot>

            <x-dropdown-link :href="route('ttlock.callback.histories.export', array_merge(request()->query(), ['format' => 'excel']))">
              <x-icon name="excel" class="w-4 h-4 mr-2" />
              Export Excel
            </x-dropdown-link>
            <x-dropdown-link :href="route('ttlock.callback.histories.export', array_merge(request()->query(), ['format' => 'pdf']))">
              <x-icon name="pdf" class="w-4 h-4 mr-2" />
              Export PDF
            </x-dropdown-link>
          </x-dropdown>

          <!-- Per Page Selector -->
          <div class="flex items-center space-x-2">
            <x-per-page-selector :options="[10, 25, 50, 100]" :current="$histories->perPage()" :url="route('ttlock.callback.histories.index')" type="server" />
          </div>
        </div>
      </div>
    </div>
  </x-card>

  <!-- Filters Card -->
  <x-card class="mb-4">
    <div class="p-4">
      <form method="GET" action="{{ route('ttlock.callback.histories.index') }}">
        @if (request()->has('search'))
          <input type="hidden" name="search" value="{{ request()->get('search') }}">
        @endif
        @if (request()->has('per_page'))
          <input type="hidden" name="per_page" value="{{ request()->get('per_page') }}">
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
          <x-select
            name="event_type"
            label="Event Type"
            :options="[
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
            ]"
            selected="{{ request()->get('event_type') }}"
            placeholder="All Event Types" />

          <x-select
            name="processed"
            label="Processed"
            :options="['1' => 'Yes', '0' => 'No']"
            selected="{{ request()->get('processed') }}"
            placeholder="All Status" />

          <x-select
            name="username"
            label="User"
            :options="array_combine($usernames ?? [], $usernames ?? [])"
            selected="{{ request()->get('username') }}"
            placeholder="All Users" />

          <div class="grid grid-cols-2 gap-2">
            <x-input name="date_from" label="From" type="date" value="{{ request('date_from') }}" />
            <x-input name="date_to" label="To" type="date" value="{{ request('date_to') }}" />
          </div>
          <div class="flex items-end">
            <x-button type="submit" color="blue">Apply Filters</x-button>
          </div>
        </div>
      </form>
    </div>
  </x-card>

  <div class="mt-6">
    <x-card title="Results">
      <x-table :headers="['Date','Lock','Event','Record Type','Battery','User','Processed']">
        @forelse($histories as $h)
          <tr>
            <td class="px-6 py-3 text-sm text-gray-700">{{ $h->created_at->format('Y-m-d H:i:s') }}</td>
            <td class="px-6 py-3 text-sm">
              <div class="font-semibold">{{ $h->lock_id }}</div>
              <div class="text-gray-500">{{ $h->lock_mac }}</div>
            </td>
            <td class="px-6 py-3 text-sm">
              <div class="font-semibold">{{ $h->event_type_description }}</div>
              <div class="text-gray-500">{{ $h->message }}</div>
            </td>
            <td class="px-6 py-3 text-sm">{{ $h->record_type_description ?? '-' }}</td>
            <td class="px-6 py-3 text-sm">{{ $h->electric_quantity }}% ({{ $h->battery_level_description }})</td>
            <td class="px-6 py-3 text-sm">{{ $h->username ?? '-' }}</td>
            <td class="px-6 py-3 text-sm">
              @if($h->processed)
                <x-badge color="green">Yes</x-badge>
              @else
                <x-badge color="yellow">No</x-badge>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-6 py-6 text-center text-gray-500">No data found.</td>
          </tr>
        @endforelse
      </x-table>

      <div class="mt-4 flex items-center justify-between">
        <div class="ml-auto">
          <x-pagination :paginator="$histories" />
        </div>
      </div>
    </x-card>
  </div>
</div>
@endsection


