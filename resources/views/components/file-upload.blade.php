@props([
    'label' => null,
    'name',
    'accept' => null,
    'required' => false,
    'multiple' => false,
    'error' => null,
    'hint' => null,
])

<div class="space-y-1.5">
  @if ($label)
    <label class="block text-sm font-medium text-gray-700 mb-1.5">
      {{ $label }}
      @if ($required)
        <span class="text-red-500 ml-0.5">*</span>
      @endif
    </label>
  @endif

  <div class="relative">
    <label for="{{ $name }}"
      class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-all duration-200 {{ $error ? 'border-red-500' : 'hover:border-blue-500' }}">
      <div class="flex flex-col items-center justify-center pt-5 pb-6">
        <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        <p class="mb-2 text-sm text-gray-500">
          <span class="font-semibold">Click to upload</span> or drag and drop
        </p>
        @if ($hint)
          <p class="text-xs text-gray-500">{{ $hint }}</p>
        @endif
      </div>

      <input type="file" name="{{ $name }}" id="{{ $name }}" {{ $accept ? "accept=$accept" : '' }}
        {{ $required ? 'required' : '' }} {{ $multiple ? 'multiple' : '' }} class="hidden"
        onchange="updateFileName(this)">
    </label>

    <div id="{{ $name }}_filename" class="mt-2 text-sm text-gray-600 hidden"></div>
  </div>

  @if ($error)
    <p class="text-sm text-red-600 flex items-center">
      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      {{ $error }}
    </p>
  @endif

  @error($name)
    <p class="text-sm text-red-600 flex items-center">
      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      {{ $message }}
    </p>
  @enderror
</div>

@once
  @push('scripts')
    <script>
      function updateFileName(input) {
        const filenameDiv = document.getElementById(input.id + '_filename');
        if (input.files.length > 0) {
          const files = Array.from(input.files).map(f => f.name).join(', ');
          filenameDiv.textContent = 'Selected: ' + files;
          filenameDiv.classList.remove('hidden');
        } else {
          filenameDiv.classList.add('hidden');
        }
      }
    </script>
  @endpush
@endonce
