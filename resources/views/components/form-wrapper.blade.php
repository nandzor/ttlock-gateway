@props([
    'title' => null,
    'subtitle' => null,
    'method' => 'POST',
    'action' => null,
    'class' => '',
    'showCsrf' => true,
    'enctype' => null,
])

<form method="{{ $method }}" action="{{ $action }}"
  @if ($enctype) enctype="{{ $enctype }}" @endif
  {{ $attributes->merge(['class' => 'space-y-6 ' . $class]) }}>
  @if ($showCsrf && $method === 'POST')
    @csrf
  @endif

  @if ($method === 'PUT' || $method === 'PATCH' || $method === 'DELETE')
    @method($method)
  @endif

  @if ($title || $subtitle)
    <div class="text-center mb-8">
      @if ($title)
        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $title }}</h2>
      @endif
      @if ($subtitle)
        <p class="text-gray-600">{{ $subtitle }}</p>
      @endif
    </div>
  @endif

  {{ $slot }}
</form>
