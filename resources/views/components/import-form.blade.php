@props([
  'action',
  'templateRoute' => null,
  'accept' => '.xlsx,.xls',
  'title' => 'Import Data',
])

<x-card>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
      @if($templateRoute)
        <x-button :href="$templateRoute" variant="outline" size="sm">
          <x-icon name="download" class="w-4 h-4 mr-1" />
          Download Template
        </x-button>
      @endif
    </div>

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-4">
      @csrf
      <div>
        <label class="block text-sm font-medium text-gray-700">File (.xlsx)</label>
        <input type="file" name="file" accept="{{ $accept }}" class="mt-1 block w-full text-sm" required />
        <x-input-error :messages="$errors->get('file')" />
      </div>

      <div class="flex items-center justify-end space-x-3">
        <x-button type="submit" variant="primary">
          <x-icon name="upload" class="w-4 h-4 mr-1" />
          Import
        </x-button>
      </div>
    </form>
  </div>
</x-card>


