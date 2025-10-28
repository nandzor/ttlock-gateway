@props(['messages' => []])

@if ($messages)
  <ul {{ $attributes->merge(['class' => 'mt-1 text-sm text-red-600 space-y-0.5']) }}>
    @foreach ((array) $messages as $message)
      <li class="flex items-start">
        <svg class="w-4 h-4 mr-1.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ $message }}</span>
      </li>
    @endforeach
  </ul>
@endif


