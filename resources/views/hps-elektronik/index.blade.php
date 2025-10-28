@extends('layouts.app')

@section('title', 'HPS Elektronik')
@section('page-title', 'HPS Elektronik Management')

@section('content')
  <!-- Statistics Cards -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <x-stat-card title="Total Items" :value="$statistics['total_items']" color="blue" :icon="'<x-icon name=\'electronics\' class=\'w-6 h-6\' />'" />

    <x-stat-card title="Active Items" :value="$statistics['active_items']" color="green" :icon="'<x-icon name=\'check\' class=\'w-6 h-6\' />'" />

    <x-stat-card title="Total Value" :value="format_currency_id($statistics['total_value'])" color="purple" :icon="'<x-icon name=\'gold\' class=\'w-6 h-6\' />'" />

    <x-stat-card title="Average Value" :value="format_currency_id($statistics['average_value'])" color="orange" :icon="'<x-icon name=\'settings\' class=\'w-6 h-6\' />'" />
  </div>

  <!-- Search and Actions Card -->
  <x-card class="mb-4">
    <div class="p-4">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-3 lg:space-y-0">
        <div class="flex-1 max-w-md">
          <form method="GET" action="{{ route('hps-elektronik.index') }}" class="flex">
            <x-input name="search" :value="$search ?? ''" placeholder="Search HPS Elektronik..." class="rounded-r-none border-r-0" />
            @if (request()->has('per_page'))
              <input type="hidden" name="per_page" value="{{ request()->get('per_page') }}">
            @endif
            @if (request()->has('kdwilayah'))
              <input type="hidden" name="kdwilayah" value="{{ request()->get('kdwilayah') }}">
            @endif
            @if (request()->has('jenis_barang'))
              <input type="hidden" name="jenis_barang" value="{{ request()->get('jenis_barang') }}">
            @endif
            @if (request()->has('merek'))
              <input type="hidden" name="merek" value="{{ request()->get('merek') }}">
            @endif
            @if (request()->has('grade'))
              <input type="hidden" name="grade" value="{{ request()->get('grade') }}">
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

            <x-dropdown-link :href="route('hps-elektronik.export', array_merge(request()->query(), ['format' => 'excel']))">
              <x-icon name="excel" class="w-4 h-4 mr-2" />
              Export Excel
            </x-dropdown-link>
            <x-dropdown-link :href="route('hps-elektronik.export', array_merge(request()->query(), ['format' => 'pdf']))">
              <x-icon name="pdf" class="w-4 h-4 mr-2" />
              Export PDF
            </x-dropdown-link>
          </x-dropdown>

          <!-- Per Page Selector -->
          <div class="flex items-center space-x-2">
            <x-per-page-selector :options="$perPageOptions ?? [10, 25, 50, 100]" :current="$perPage ?? 10" :url="route('hps-elektronik.index')" type="server" />
          </div>

          <!-- Import & Add Buttons -->
          <x-button :href="route('hps-elektronik.import.form')" variant="secondary" size="sm">
            <x-icon name="upload" class="w-4 h-4 mr-1.5" />
            Import
          </x-button>
          <x-button variant="primary" size="sm" :href="route('hps-elektronik.create')">
            <x-icon name="plus" class="w-4 h-4 mr-1.5" />
            Add HPS Elektronik
          </x-button>
        </div>
      </div>
    </div>
  </x-card>

  <!-- Filters Card -->
  <x-card class="mb-4">
    <div class="p-4">
      <form method="GET" action="{{ route('hps-elektronik.index') }}">
        @if (request()->has('search'))
          <input type="hidden" name="search" value="{{ request()->get('search') }}">
        @endif
        @if (request()->has('per_page'))
          <input type="hidden" name="per_page" value="{{ request()->get('per_page') }}">
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3">
          <x-filter-select
            name="kdwilayah"
            label="Wilayah"
            :options="$filterOptions['wilayah']"
            :selected="request()->get('kdwilayah')"
            placeholder="All Wilayah"
            :autoSubmit="true" />

          <x-filter-select
            name="jenis_barang"
            label="Jenis Barang"
            :options="$filterOptions['jenis_barang']"
            :selected="request()->get('jenis_barang')"
            placeholder="All Jenis Barang"
            :autoSubmit="true" />

          <x-filter-select
            name="merek"
            label="Merek"
            :options="$filterOptions['merek']"
            :selected="request()->get('merek')"
            placeholder="All Merek"
            :autoSubmit="true" />

          <x-filter-select
            name="grade"
            label="Grade"
            :options="$filterOptions['grade']"
            :selected="request()->get('grade')"
            placeholder="All Grade"
            :autoSubmit="true" />

          <x-filter-select
            name="tahun"
            label="Tahun"
            :options="$filterOptions['tahun']"
            :selected="request()->get('tahun')"
            placeholder="All Tahun"
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
    <x-table :headers="['Wilayah', 'Jenis Barang', 'Merek', 'Barang', 'Tahun', 'Harga', 'Status', 'Grade', 'Kondisi', 'Actions']">
      @forelse($hpsElektronik as $item)
        <tr class="hover:bg-blue-50 transition-colors">
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center">
              <div class="flex-shrink-0 h-10 w-10">
                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center shadow-md">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-900">{{ $item->kdwilayah }}</div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">{{ $item->jenis_barang }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">{{ $item->merek }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">{{ Str::limit($item->barang, 30) }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            {{ $item->tahun }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            {{ format_currency_id($item->harga) }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <x-badge :variant="$item->active ? 'success' : 'danger'">
              {{ $item->active ? 'Active' : 'Inactive' }}
            </x-badge>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            @if($item->grade)
              @php
                $gradeColors = [
                  'A' => 'info',
                  'B' => 'success',
                  'C' => 'warning',
                  'D' => 'danger',

                ];
                $badgeColor = $gradeColors[$item->grade] ?? 'secondary';
              @endphp
              <x-badge :variant="$badgeColor">
                {{ $item->grade }}
              </x-badge>
            @else
              <span class="text-gray-400">-</span>
            @endif
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            @if($item->kondisi)
              <div class="max-w-xs">
                <span class="inline-block bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">
                  {{ Str::limit($item->kondisi, 30) }}
                </span>
              </div>
            @else
              <span class="text-gray-400">-</span>
            @endif
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <x-action-dropdown>
              <x-dropdown-link :href="route('hps-elektronik.show', $item)">
                👁️ View Details
              </x-dropdown-link>

              <x-dropdown-link :href="route('hps-elektronik.edit', $item)">
                ✏️ Edit Item
              </x-dropdown-link>

              <x-dropdown-divider />

              <x-dropdown-link :href="route('hps-elektronik.toggle', $item)" onclick="event.preventDefault(); document.getElementById('toggle-form-{{ $item->id }}').submit();">
                {{ $item->active ? '🔴 Deactivate' : '🟢 Activate' }}
              </x-dropdown-link>

              <form id="toggle-form-{{ $item->id }}" action="{{ route('hps-elektronik.toggle', $item) }}" method="POST" class="hidden">
                @csrf
              </form>

              <x-dropdown-divider />

              <x-dropdown-button type="button" onclick="confirmDelete({{ $item->id }})" variant="danger">
                🗑️ Delete Item
              </x-dropdown-button>

              <form id="delete-form-{{ $item->id }}" action="{{ route('hps-elektronik.destroy', $item->id) }}" method="POST"
                class="hidden">
                @csrf
                @method('DELETE')
              </form>
            </x-action-dropdown>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="9" class="px-6 py-12 text-center">
            <div class="flex flex-col items-center justify-center">
              <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
              </svg>
              <p class="text-gray-500 text-lg font-medium">No HPS Elektronik items found</p>
              <p class="text-gray-400 text-sm mt-1">Try adjusting your search criteria</p>
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
          <span class="font-medium">{{ $hpsElektronik->firstItem() ?? 0 }}</span>
          to
          <span class="font-medium">{{ $hpsElektronik->lastItem() ?? 0 }}</span>
          of
          <span class="font-medium">{{ $hpsElektronik->total() }}</span>
          results
          @if (request()->has('search'))
            for "<span class="font-medium text-blue-600">{{ request()->get('search') }}</span>"
          @endif
        </div>

        <!-- Pagination Controls -->
        @if ($hpsElektronik->hasPages())
          <x-pagination :paginator="$hpsElektronik" />
        @endif
      </div>
    </div>
  </x-card>

  <!-- Delete Confirmation Modal -->
  <x-confirm-modal id="confirm-delete" title="Confirm Delete"
    message="This action cannot be undone. The HPS Elektronik item will be permanently deleted." confirmText="Delete Item"
    cancelText="Cancel" icon="warning" confirmAction="handleDeleteConfirm(data)" />
@endsection

@push('scripts')
  <script>
    // Store itemId for deletion
    let pendingDeleteItemId = null;

    function confirmDelete(itemId) {
      pendingDeleteItemId = itemId;
      // Dispatch event to open modal with itemId
      window.dispatchEvent(new CustomEvent('open-modal-confirm-delete', {
        detail: {
          itemId: itemId
        }
      }));
    }

    function handleDeleteConfirm(data) {
      const itemId = data?.itemId || pendingDeleteItemId;
      if (itemId) {
        const form = document.getElementById('delete-form-' + itemId);
        if (form) {
          form.submit();
        }
      }
    }

    // Make functions globally available
    window.confirmDelete = confirmDelete;
    window.handleDeleteConfirm = handleDeleteConfirm;
  </script>
@endpush
