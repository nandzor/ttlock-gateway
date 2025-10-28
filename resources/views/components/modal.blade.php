@props(['id', 'title' => '', 'size' => 'md', 'footer' => null, 'closeable' => true, 'closeOnBackdrop' => true])

@php
  $sizes = [
      'sm' => 'max-w-md',
      'md' => 'max-w-2xl',
      'lg' => 'max-w-4xl',
      'xl' => 'max-w-6xl',
      'full' => 'max-w-full mx-4',
  ];
@endphp

<div x-data="{ show: false, data: null }" x-on:open-modal-{{ $id }}.window="show = true; data = $event.detail || null"
  x-on:close-modal-{{ $id }}.window="show = false; data = null"
  x-on:keydown.escape.window="if(show && {{ $closeable ? 'true' : 'false' }}) { show = false; data = null; }">

  <div x-show="show" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
    <!-- Backdrop -->
    <div x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
      x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
      class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm"
      @if ($closeOnBackdrop) @click="show = false; data = null;" @endif>
    </div>

    <!-- Modal -->
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
      <div x-show="show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="relative inline-block w-full {{ $sizes[$size] }} p-6 my-8 text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl"
        @if ($closeOnBackdrop) @click.away="show = false; data = null;" @endif>

        <!-- Header -->
        @if ($title)
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-2xl font-bold text-gray-900">{{ $title }}</h3>
            @if ($closeable)
              <button @click="show = false; data = null;" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            @endif
          </div>
        @endif

        <!-- Content -->
        <div class="{{ $title ? 'mt-4' : '' }}">
          {{ $slot }}
        </div>

        <!-- Footer -->
        @if ($footer !== null)
          <div class="mt-6 flex items-center justify-end space-x-3">
            {{ $footer }}
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
