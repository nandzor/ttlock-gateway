@props([
    'id',
    'title' => 'Confirm Action',
    'message' => 'Are you sure you want to proceed?',
    'confirmText' => 'Confirm',
    'cancelText' => 'Cancel',
    'icon' => 'warning',
    'confirmAction' => null,
])

@php
  $iconColors = [
      'warning' => 'bg-red-100 text-red-600',
      'info' => 'bg-blue-100 text-blue-600',
      'success' => 'bg-green-100 text-green-600',
      'danger' => 'bg-red-100 text-red-600',
  ];

  $iconPaths = [
      'warning' =>
          'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
      'info' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
      'success' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
      'danger' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
  ];
@endphp

<div x-data="{ show: false, data: null }" x-on:open-modal-{{ $id }}.window="show = true; data = $event.detail || null"
  x-on:close-modal-{{ $id }}.window="show = false; data = null"
  x-on:keydown.escape.window="if(show) { show = false; data = null; }">

  <div x-show="show" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
    <!-- Backdrop -->
    <div x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
      x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
      class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm" @click="show = false; data = null">
    </div>

    <!-- Modal -->
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
      <div x-show="show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="relative inline-block w-full max-w-md p-6 my-8 text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl"
        @click.away="show = false; data = null;">

        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-2xl font-bold text-gray-900">{{ $title }}</h3>
          <button type="button" @click="show = false; data = null"
            class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div class="text-center py-4">
          <!-- Icon -->
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full {{ $iconColors[$icon] }} mb-4">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths[$icon] }}" />
            </svg>
          </div>

          <!-- Message -->
          @if ($slot->isEmpty())
            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $title }}</h3>
            <p class="text-sm text-gray-500">{{ $message }}</p>
          @else
            {{ $slot }}
          @endif
        </div>

        <!-- Footer -->
        <div class="mt-6 flex items-center justify-end space-x-3">
          <x-button variant="secondary" @click="show = false; data = null">
            {{ $cancelText }}
          </x-button>

          @if ($confirmAction)
            <x-button variant="danger" @click="{{ $confirmAction }}">
              {{ $confirmText }}
            </x-button>
          @else
            <x-button variant="danger"
              @click="$dispatch('confirm-{{ $id }}', data); show = false; data = null">
              {{ $confirmText }}
            </x-button>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
