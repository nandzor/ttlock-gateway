@extends('layouts.app')

@section('title', 'Detail HPS Emas')
@section('page-title', 'Detail HPS Emas')

@section('content')
  <x-card>
    <div class="p-6">
          <!-- Header Info -->
          <div class="flex items-center mb-6">
            <div class="flex-shrink-0 h-16 w-16">
              <div class="h-16 w-16 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center shadow-lg">
                <x-icon name="gold" class="w-8 h-8 text-white" />
              </div>
            </div>
            <div class="ml-6">
              <h3 class="text-2xl font-bold text-gray-900">{{ $hpsEmas->jenis_barang }}</h3>
              <p class="text-sm text-gray-500">ID: {{ $hpsEmas->id }}</p>
              <div class="mt-2">
                <x-badge :variant="$hpsEmas->active ? 'success' : 'danger'">
                  {{ $hpsEmas->active ? 'Active' : 'Inactive' }}
                </x-badge>
              </div>
            </div>
          </div>

          <!-- Details Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Jenis Barang -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="text-sm font-medium text-gray-500 mb-2">Jenis Barang</h4>
              <p class="text-lg font-semibold text-gray-900">{{ $hpsEmas->jenis_barang }}</p>
            </div>

            <!-- STLE (Rp) -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="text-sm font-medium text-gray-500 mb-2">STLE (Rp)</h4>
              <p class="text-lg font-semibold text-gray-900">{{ format_currency_id($hpsEmas->stle_rp) }}</p>
            </div>

            <!-- Kadar Karat -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="text-sm font-medium text-gray-500 mb-2">Kadar Karat</h4>
              <p class="text-lg font-semibold text-gray-900">{{ $hpsEmas->kadar_karat }}K</p>
            </div>

            <!-- Berat (Gram) -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="text-sm font-medium text-gray-500 mb-2">Berat (Gram)</h4>
              <p class="text-lg font-semibold text-gray-900">{{ number_format($hpsEmas->berat_gram, 2) }}g</p>
            </div>

            <!-- Nilai Taksiran (Rp) -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="text-sm font-medium text-gray-500 mb-2">Nilai Taksiran (Rp)</h4>
              <p class="text-lg font-semibold text-gray-900">{{ format_currency_id($hpsEmas->nilai_taksiran_rp) }}</p>
            </div>

            <!-- LTV (%) -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="text-sm font-medium text-gray-500 mb-2">LTV (%)</h4>
              <p class="text-lg font-semibold text-gray-900">{{ number_format($hpsEmas->ltv, 2) }}%</p>
            </div>

            <!-- Uang Pinjaman (Rp) -->
            <div class="bg-gray-50 p-4 rounded-lg md:col-span-2 lg:col-span-3">
              <h4 class="text-sm font-medium text-gray-500 mb-2">Uang Pinjaman (Rp)</h4>
              <p class="text-lg font-semibold text-gray-900">{{ format_currency_id($hpsEmas->uang_pinjaman_rp) }}</p>
            </div>
          </div>

          <!-- Timestamps -->
          <div class="mt-8 pt-6 border-t border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-500">
              <div>
                <span class="font-medium">Dibuat:</span>
                {{ $hpsEmas->created_at?->format('d/m/Y H:i:s') ?? '-' }}
              </div>
              <div>
                <span class="font-medium">Diperbarui:</span>
                {{ $hpsEmas->updated_at?->format('d/m/Y H:i:s') ?? '-' }}
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="mt-8 flex justify-end space-x-4">
            <form action="{{ route('hps-emas.toggle', ['hpsEmas' => $hpsEmas->id]) }}" method="POST" class="inline">
              @csrf
              @method('PATCH')
              <x-button type="submit" :variant="$hpsEmas->active ? 'danger' : 'success'" size="md">
                <x-icon name="{{ $hpsEmas->active ? 'x-circle' : 'check-circle' }}" class="w-4 h-4 inline mr-1" />
                {{ $hpsEmas->active ? 'Deactivate' : 'Activate' }}
              </x-button>
            </form>

            <form action="{{ route('hps-emas.destroy', ['hpsEmas' => $hpsEmas->id]) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus HPS Emas ini?')">
              @csrf
              @method('DELETE')
              <x-button type="submit" variant="danger" size="md">
                <x-icon name="trash" class="w-4 h-4 inline mr-1" />
                Delete
              </x-button>
            </form>
          </div>
    </div>
  </x-card>
@endsection
