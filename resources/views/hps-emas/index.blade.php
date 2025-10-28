@extends('layouts.app')

@section('title', 'HPS Emas')
@section('page-title', 'HPS Emas Management')

@section('content')
  <!-- Statistics Cards -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <x-stat-card title="Total Items" :value="$statistics['total_items']" color="blue" :icon="'<x-icon name=\'gold\' class=\'w-6 h-6\' />'" />

    <x-stat-card title="Active Items" :value="$statistics['active_items']" color="green" :icon="'<x-icon name=\'check\' class=\'w-6 h-6\' />'" />

    <x-stat-card title="Total Value" :value="format_currency_id($statistics['total_value'])" color="purple" :icon="'<x-icon name=\'currency-dollar\' class=\'w-6 h-6\' />'" />

    <x-stat-card title="Average Value" :value="format_currency_id($statistics['average_value'])" color="orange" :icon="'<x-icon name=\'chart-bar\' class=\'w-6 h-6\' />'" />
  </div>

  <!-- Search and Actions Card -->
  <x-card class="mb-4">
    <div class="p-4">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-3 lg:space-y-0">
        <div class="flex-1 max-w-md">
          <form method="GET" action="{{ route('hps-emas.index') }}" class="flex">
            <x-input name="search" :value="$search ?? ''" placeholder="Search HPS Emas..." class="rounded-r-none border-r-0" />
            @if (request()->has('per_page'))
              <input type="hidden" name="per_page" value="{{ request()->get('per_page') }}">
            @endif
            @if (request()->has('jenis_barang'))
              <input type="hidden" name="jenis_barang" value="{{ request()->get('jenis_barang') }}">
            @endif
            @if (request()->has('kadar_karat'))
              <input type="hidden" name="kadar_karat" value="{{ request()->get('kadar_karat') }}">
            @endif
            @if (request()->has('active'))
              <input type="hidden" name="active" value="{{ request()->get('active') }}">
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

            <x-dropdown-link :href="route('hps-emas.export', array_merge(request()->query(), ['format' => 'excel']))">
              <x-icon name="excel" class="w-4 h-4 mr-2" />
              Export Excel
            </x-dropdown-link>
            <x-dropdown-link :href="route('hps-emas.export', array_merge(request()->query(), ['format' => 'pdf']))">
              <x-icon name="pdf" class="w-4 h-4 mr-2" />
              Export PDF
            </x-dropdown-link>
          </x-dropdown>

          <!-- Per Page Selector -->
          <div class="flex items-center space-x-2">
            <x-per-page-selector :options="$perPageOptions ?? [10, 25, 50, 100]" :current="$perPage ?? 10" :url="route('hps-emas.index')" type="server" />
          </div>

          <!-- Import & Add Buttons -->
          <x-button :href="route('hps-emas.import.form')" variant="secondary" size="sm">
            <x-icon name="upload" class="w-4 h-4 mr-1.5" />
            Import
          </x-button>
          <a href="{{ route('hps-emas.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
            <x-icon name="plus" class="w-4 h-4 inline mr-1" />
            Add HPS Emas
          </a>
        </div>
      </div>
    </div>
  </x-card>

  <!-- Filters Card -->
  <x-card class="mb-4">
    <div class="p-4">
      <form method="GET" action="{{ route('hps-emas.index') }}">
        @if (request()->has('search'))
          <input type="hidden" name="search" value="{{ request()->get('search') }}">
        @endif
        @if (request()->has('per_page'))
          <input type="hidden" name="per_page" value="{{ request()->get('per_page') }}">
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
          <x-filter-select
            name="jenis_barang"
            label="Jenis Barang"
            :options="$filterOptions['jenis_barang']"
            :selected="request()->get('jenis_barang')"
            placeholder="All Jenis Barang"
            :autoSubmit="true" />

          <x-filter-select
            name="kadar_karat"
            label="Kadar Karat"
            :options="$filterOptions['kadar_karat']"
            :selected="request()->get('kadar_karat')"
            placeholder="All Kadar Karat"
            :autoSubmit="true" />

          <x-filter-select
            name="active"
            label="Status"
            :options="['1' => 'Active', '0' => 'Inactive']"
            :selected="request()->get('active')"
            placeholder="All Status"
            :autoSubmit="true" />
        </div>
      </form>
    </div>
  </x-card>

  <!-- Table Card -->
  <x-card>

    <!-- Table -->
    <x-table :headers="['Jenis Barang', 'STLE (Rp)', 'Kadar Karat', 'Berat (Gram)', 'Nilai Taksiran (Rp)', 'LTV (%)', 'Uang Pinjaman (Rp)', 'Status', 'Actions']">
        @forelse($hpsEmas as $item)
          <tr class="hover:bg-blue-50 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10">
                  <div class="h-10 w-10 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center shadow-md">
                    <x-icon name="gold" class="w-5 h-5 text-white" />
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">{{ $item->jenis_barang }}</div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ format_currency_id($item->stle_rp) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ $item->kadar_karat }}K
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ number_format($item->berat_gram, 2) }}g
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ format_currency_id($item->nilai_taksiran_rp) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ number_format($item->ltv, 2) }}%
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ format_currency_id($item->uang_pinjaman_rp) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <x-badge :variant="$item->active ? 'success' : 'danger'">
                {{ $item->active ? 'Active' : 'Inactive' }}
              </x-badge>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <x-action-dropdown>
                <x-dropdown-link :href="route('hps-emas.show', $item)">
                  <x-icon name="eye" class="w-4 h-4 mr-2" />
                  View Details
                </x-dropdown-link>

                <x-dropdown-link :href="route('hps-emas.edit', $item)">
                  <x-icon name="pencil" class="w-4 h-4 mr-2" />
                  Edit Item
                </x-dropdown-link>

                <x-dropdown-divider />

              <x-dropdown-link :href="route('hps-emas.toggle', ['hpsEmas' => $item->id])" onclick="event.preventDefault(); document.getElementById('toggle-form-{{ $item->id }}').submit();">
                  <x-icon name="{{ $item->active ? 'x-circle' : 'check-circle' }}" class="w-4 h-4 mr-2" />
                  {{ $item->active ? 'Deactivate' : 'Activate' }}
                </x-dropdown-link>

              <x-dropdown-link :href="route('hps-emas.destroy', ['hpsEmas' => $item->id])" onclick="event.preventDefault(); document.getElementById('delete-form-{{ $item->id }}').submit();" class="text-red-600 hover:text-red-800">
                  <x-icon name="trash" class="w-4 h-4 mr-2" />
                  Delete
                </x-dropdown-link>
              </x-action-dropdown>

              <!-- Hidden Forms -->
              <form id="toggle-form-{{ $item->id }}" action="{{ route('hps-emas.toggle', ['hpsEmas' => $item->id]) }}" method="POST" class="hidden">
                @csrf
                @method('PATCH')
              </form>

              <form id="delete-form-{{ $item->id }}" action="{{ route('hps-emas.destroy', ['hpsEmas' => $item->id]) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="px-6 py-4 text-center text-gray-500">
              <div class="flex flex-col items-center">
                <x-icon name="inbox" class="w-12 h-12 text-gray-400 mb-2" />
                <p>Tidak ada data HPS Emas ditemukan.</p>
              </div>
            </td>
          </tr>
        @endforelse
      </x-table>

    <!-- Pagination Info & Controls -->
    <div class="px-6 py-4 border-t border-gray-200">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
        <!-- Pagination Info -->
        <div class="text-sm text-gray-700">
          Showing
          <span class="font-medium">{{ $hpsEmas->firstItem() ?? 0 }}</span>
          to
          <span class="font-medium">{{ $hpsEmas->lastItem() ?? 0 }}</span>
          of
          <span class="font-medium">{{ $hpsEmas->total() }}</span>
          results
          @if (request()->has('search'))
            for "<span class="font-medium text-blue-600">{{ request()->get('search') }}</span>"
          @endif
        </div>

        <!-- Pagination Controls -->
        @if ($hpsEmas->hasPages())
          <x-pagination :paginator="$hpsEmas" />
        @endif
      </div>
    </div>
  </x-card>
@endsection
