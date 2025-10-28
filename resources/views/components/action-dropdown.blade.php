@props(['align' => 'right'])

<x-dropdown :align="$align" width="48">
  <x-slot name="trigger">
    <button type="button"
      class="inline-flex items-center justify-center w-10 h-10 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500">
      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
      </svg>
    </button>
  </x-slot>

  {{ $slot }}
</x-dropdown>
