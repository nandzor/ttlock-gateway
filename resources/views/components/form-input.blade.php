@props(['label', 'name', 'type' => 'text', 'value' => '', 'required' => false, 'placeholder' => ''])

<div class="mb-4">
  <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-2">
    {{ $label }}
    @if ($required)
      <span class="text-red-500">*</span>
    @endif
  </label>

  @if ($type === 'textarea')
    <textarea id="{{ $name }}" name="{{ $name }}" rows="3" {{ $required ? 'required' : '' }}
      placeholder="{{ $placeholder }}"
      {{ $attributes->merge(['class' => 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent']) }}>{{ old($name, $value) }}</textarea>
  @elseif($type === 'select')
    <select id="{{ $name }}" name="{{ $name }}" {{ $required ? 'required' : '' }}
      {{ $attributes->merge(['class' => 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent']) }}>
      {{ $slot }}
    </select>
  @else
    <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}"
      value="{{ old($name, $value) }}" {{ $required ? 'required' : '' }} placeholder="{{ $placeholder }}"
      {{ $attributes->merge(['class' => 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent']) }} />
  @endif

  @error($name)
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
  @enderror
</div>
