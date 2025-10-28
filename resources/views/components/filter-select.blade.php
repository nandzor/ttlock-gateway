@props([
    'label' => null,
    'name',
    'options' => [],
    'selected' => '',
    'placeholder' => 'All',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'hint' => null,
    'autoSubmit' => false,
])

<div class="space-y-1.5">
  @if ($label)
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
      {{ $label }}
      @if ($required)
        <span class="text-red-500 ml-0.5">*</span>
      @endif
    </label>
  @endif

  <select name="{{ $name }}" id="{{ $name }}" {{ $required ? 'required' : '' }}
    {{ $disabled ? 'disabled' : '' }}
    @if($autoSubmit) onchange="this.form.submit()" @endif
    {{ $attributes->merge(['class' => 'w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-all duration-200 ' . ($error ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : '') . ($disabled ? ' bg-gray-100 cursor-not-allowed' : '')]) }}>
    @if ($placeholder)
      <option value="">{{ $placeholder }}</option>
    @endif

    @foreach ($options as $value => $label)
      <option value="{{ $value }}" {{ old($name, $selected) == $value ? 'selected' : '' }}>
        {{ $label }}
      </option>
    @endforeach
  </select>

  @if ($hint && !$error)
    <p class="text-sm text-gray-600 mt-1">{{ $hint }}</p>
  @endif

  @if ($error)
    <p class="text-sm text-red-600 flex items-center mt-1">
      <x-icon name="warning" class="w-4 h-4 mr-1.5" />
      {{ $error }}
    </p>
  @endif

  @error($name)
    <p class="text-sm text-red-600 flex items-center mt-1">
      <x-icon name="warning" class="w-4 h-4 mr-1.5" />
      {{ $message }}
    </p>
  @enderror
</div>
