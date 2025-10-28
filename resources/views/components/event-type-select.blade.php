@props([
    'name' => 'event_type',
    'label' => 'Event Type',
    'value' => null,
    'placeholder' => 'All Types',
    'required' => false,
])

<div>
  @if ($label)
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1.5">
      {{ $label }}
      @if ($required)
        <span class="text-red-500">*</span>
      @endif
    </label>
  @endif

  <select name="{{ $name }}" id="{{ $name }}"
    {{ $attributes->merge([
        'class' =>
            'block w-full px-3 py-2 text-sm rounded-lg border-gray-300 shadow-sm transition-all duration-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500 focus:ring-opacity-20',
    ]) }}
    {{ $required ? 'required' : '' }}>

    @if ($placeholder)
      <option value="">{{ $placeholder }}</option>
    @endif

    <option value="detection" {{ $value === 'detection' ? 'selected' : '' }}>
      🔍 Detection
    </option>
    <option value="alert" {{ $value === 'alert' ? 'selected' : '' }}>
      🚨 Alert
    </option>
    <option value="motion" {{ $value === 'motion' ? 'selected' : '' }}>
      🎥 Motion
    </option>
    <option value="manual" {{ $value === 'manual' ? 'selected' : '' }}>
      👤 Manual
    </option>
  </select>

  {{ $slot }}
</div>
