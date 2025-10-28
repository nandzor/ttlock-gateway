@extends('layouts.app')

@section('title', 'HPS Elektronik Details')
@section('page-title', 'HPS Elektronik Details')

@section('content')
  <x-card>
    <div class="p-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">{{ $hpsElektronik->barang }}</h2>
          <p class="text-sm text-gray-600">ID: {{ $hpsElektronik->id }}</p>
        </div>
        <div class="flex items-center space-x-2">
          <x-button variant="secondary" :href="route('hps-elektronik.index')">
            <x-icon name="chevron-left" class="w-4 h-4 mr-2" />
            Back to List
          </x-button>
          <x-button variant="primary" :href="route('hps-elektronik.edit', $hpsElektronik->id)">
            <x-icon name="edit" class="w-4 h-4 mr-2" />
            Edit
          </x-button>
        </div>
      </div>

      <!-- Details -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Basic Information -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Basic Information</h3>

          <div>
            <label class="block text-sm font-medium text-gray-700">Wilayah</label>
            <p class="mt-1 text-sm text-gray-900">{{ $hpsElektronik->kdwilayah }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Jenis Barang</label>
            <p class="mt-1 text-sm text-gray-900">{{ $hpsElektronik->jenis_barang }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Merek</label>
            <p class="mt-1 text-sm text-gray-900">{{ $hpsElektronik->merek }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Barang</label>
            <p class="mt-1 text-sm text-gray-900">{{ $hpsElektronik->barang }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Tahun</label>
            <p class="mt-1 text-sm text-gray-900">{{ $hpsElektronik->tahun }}</p>
          </div>
        </div>

        <!-- Pricing & Status -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Pricing & Status</h3>

          <div>
            <label class="block text-sm font-medium text-gray-700">Harga</label>
            <p class="mt-1 text-lg font-semibold text-gray-900">Rp {{ number_format($hpsElektronik->harga, 0, ',', '.') }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Grade</label>
            <div class="mt-1">
              @if($hpsElektronik->grade)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                  {{ $hpsElektronik->grade === 'A' ? 'bg-green-100 text-green-800' :
                     ($hpsElektronik->grade === 'B' ? 'bg-yellow-100 text-yellow-800' :
                     ($hpsElektronik->grade === 'C' ? 'bg-orange-100 text-orange-800' :
                     ($hpsElektronik->grade === 'D' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'))) }}">
                  {{ $hpsElektronik->grade }}
                </span>
              @else
                <span class="text-sm text-gray-500">Not specified</span>
              @endif
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <div class="mt-1">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                {{ $hpsElektronik->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ $hpsElektronik->active ? 'Active' : 'Inactive' }}
              </span>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Created At</label>
            <p class="mt-1 text-sm text-gray-900">{{ $hpsElektronik->created_at->format('d M Y, H:i:s') }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Updated At</label>
            <p class="mt-1 text-sm text-gray-900">{{ $hpsElektronik->updated_at->format('d M Y, H:i:s') }}</p>
          </div>
        </div>
      </div>

      <!-- Kondisi -->
      @if($hpsElektronik->kondisi)
        <div class="mt-6">
          <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Kondisi</h3>
          <div class="mt-2 p-4 bg-gray-50 rounded-md">
            <p class="text-sm text-gray-900 whitespace-pre-line">{{ $hpsElektronik->kondisi }}</p>
          </div>
        </div>
      @endif

      <!-- Actions -->
      <div class="mt-8 pt-6 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <form method="POST" action="{{ route('hps-elektronik.toggle', $hpsElektronik->id) }}">
              @csrf
              <x-button variant="{{ $hpsElektronik->active ? 'warning' : 'success' }}" type="submit">
                <x-icon name="{{ $hpsElektronik->active ? 'x' : 'check' }}" class="w-4 h-4 mr-2" />
                {{ $hpsElektronik->active ? 'Deactivate' : 'Activate' }}
              </x-button>
            </form>

            <form method="POST" action="{{ route('hps-elektronik.destroy', $hpsElektronik->id) }}"
                  onsubmit="return confirm('Are you sure you want to delete this HPS Elektronik record?')">
              @csrf
              @method('DELETE')
              <x-button variant="danger" type="submit">
                <x-icon name="delete" class="w-4 h-4 mr-2" />
                Delete
              </x-button>
            </form>
          </div>

          <div class="text-sm text-gray-500">
            Last updated {{ $hpsElektronik->updated_at->diffForHumans() }}
          </div>
        </div>
      </div>
    </div>
  </x-card>
@endsection
