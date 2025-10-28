@extends('layouts.app')

@section('title', 'Edit HPS Emas')
@section('page-title', 'Edit HPS Emas')

@section('content')
<x-card>
  <div class="p-6">
    <form method="POST" action="{{ route('hps-emas.update', ['hpsEmas' => $hpsEmas->id]) }}" class="space-y-6">
      @csrf
      @method('PUT')

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Jenis Barang -->
        <div>
          <x-free-text-select id="jenis_barang" name="jenis_barang" label="Jenis Barang" class="mt-1 block w-full"
            :options="$filterOptions['jenis_barang']" :selected="old('jenis_barang', $hpsEmas->jenis_barang)"
            placeholder="Pilih Jenis Barang" required />
          <x-input-error :messages="$errors->get('jenis_barang')" class="mt-2" />
        </div>

        <!-- STLE (Rp) -->
        <div>
          <x-input id="stle_rp" name="stle_rp" label="STLE (Rp)" type="number" step="0.01" min="0" class="mt-1 block w-full"
            :value="old('stle_rp', $hpsEmas->stle_rp)" placeholder="Masukkan STLE dalam Rupiah" required />
          <x-input-error :messages="$errors->get('stle_rp')" class="mt-2" />
        </div>

        <!-- Kadar Karat -->
        <div>
          <x-free-text-select id="kadar_karat" name="kadar_karat" label="Kadar Karat" class="mt-1 block w-full"
            :options="$filterOptions['kadar_karat']" :selected="old('kadar_karat', $hpsEmas->kadar_karat)"
            placeholder="Pilih Kadar Karat" required />
          <x-input-error :messages="$errors->get('kadar_karat')" class="mt-2" />
        </div>

        <!-- Berat (Gram) -->
        <div>
          <x-input id="berat_gram" name="berat_gram" label="Berat (Gram)" type="number" step="0.01" min="0" class="mt-1 block w-full"
            :value="old('berat_gram', $hpsEmas->berat_gram)" placeholder="Masukkan Berat dalam Gram" required />
          <x-input-error :messages="$errors->get('berat_gram')" class="mt-2" />
        </div>

        <!-- Nilai Taksiran (Rp) -->
        <div>
          <x-input id="nilai_taksiran_rp" name="nilai_taksiran_rp" label="Nilai Taksiran (Rp)" type="number" step="0.01" min="0"
            class="mt-1 block w-full" :value="old('nilai_taksiran_rp', $hpsEmas->nilai_taksiran_rp)"
            placeholder="Masukkan Nilai Taksiran dalam Rupiah" required />
          <x-input-error :messages="$errors->get('nilai_taksiran_rp')" class="mt-2" />
        </div>

        <!-- LTV (%) -->
        <div>
          <x-input id="ltv" name="ltv" label="LTV (%)" type="number" step="0.01" min="0" max="100" class="mt-1 block w-full"
            :value="old('ltv', $hpsEmas->ltv)" placeholder="Masukkan LTV dalam Persen" required />
          <x-input-error :messages="$errors->get('ltv')" class="mt-2" />
        </div>

        <!-- Uang Pinjaman (Rp) -->
        <div class="md:col-span-2">
          <x-input id="uang_pinjaman_rp" name="uang_pinjaman_rp" label="Uang Pinjaman (Rp)" type="number" step="0.01" min="0"
            class="mt-1 block w-full" :value="old('uang_pinjaman_rp', $hpsEmas->uang_pinjaman_rp)"
            placeholder="Masukkan Uang Pinjaman dalam Rupiah" required />
          <x-input-error :messages="$errors->get('uang_pinjaman_rp')" class="mt-2" />
        </div>
      </div>

      <!-- Submit Buttons -->
      <div class="flex items-center justify-end space-x-3 pt-6">
        <x-button :href="route('hps-emas.index')" variant="outline" size="md">
          <x-icon name="x" class="w-4 h-4 inline mr-1" />
          Batal
        </x-button>
        <x-button type="submit" variant="primary" size="md">
          <x-icon name="check" class="w-4 h-4 inline mr-1" />
          Update
        </x-button>
      </div>
    </form>
  </div>
</x-card>
@endsection
