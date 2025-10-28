@extends('layouts.app')

@section('title', 'Create HPS Emas')
@section('page-title', 'Create HPS Emas')

@section('content')
  <x-card>
    <div class="p-6">
          <form method="POST" action="{{ route('hps-emas.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Jenis Barang -->
              <div>
                <x-free-text-select
                  id="jenis_barang"
                  name="jenis_barang"
                  label="Jenis Barang"
                  class="mt-1 block w-full"
                  :options="$filterOptions['jenis_barang']"
                  :selected="old('jenis_barang')"
                  placeholder="Pilih Jenis Barang"
                  required />
                <x-input-error :messages="$errors->get('jenis_barang')" class="mt-2" />
              </div>

              <!-- STLE (Rp) -->
              <div>
                <x-input
                  id="stle_rp"
                  name="stle_rp"
                  label="STLE (Rp)"
                  type="number"
                  step="0.01"
                  min="0"
                  class="mt-1 block w-full"
                  :value="old('stle_rp')"
                  placeholder="Masukkan STLE dalam Rupiah"
                  required />
                <x-input-error :messages="$errors->get('stle_rp')" class="mt-2" />
              </div>

              <!-- Kadar Karat -->
              <div>
                <x-free-text-select
                  id="kadar_karat"
                  name="kadar_karat"
                  label="Kadar Karat"
                  class="mt-1 block w-full"
                  :options="$filterOptions['kadar_karat']"
                  :selected="old('kadar_karat')"
                  placeholder="Pilih Kadar Karat"
                  required />
                <x-input-error :messages="$errors->get('kadar_karat')" class="mt-2" />
              </div>

              <!-- Berat (Gram) -->
              <div>
                <x-input
                  id="berat_gram"
                  name="berat_gram"
                  label="Berat (Gram)"
                  type="number"
                  step="0.01"
                  min="0"
                  class="mt-1 block w-full"
                  :value="old('berat_gram')"
                  placeholder="Masukkan Berat dalam Gram"
                  required />
                <x-input-error :messages="$errors->get('berat_gram')" class="mt-2" />
              </div>

              <!-- Nilai Taksiran (Rp) -->
              <div>
                <x-input
                  id="nilai_taksiran_rp"
                  name="nilai_taksiran_rp"
                  label="Nilai Taksiran (Rp)"
                  type="number"
                  step="0.01"
                  min="0"
                  class="mt-1 block w-full"
                  :value="old('nilai_taksiran_rp')"
                  placeholder="Masukkan Nilai Taksiran dalam Rupiah"
                  required />
                <x-input-error :messages="$errors->get('nilai_taksiran_rp')" class="mt-2" />
              </div>

              <!-- LTV (%) -->
              <div>
                <x-input
                  id="ltv"
                  name="ltv"
                  label="LTV (%)"
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  class="mt-1 block w-full"
                  :value="old('ltv')"
                  placeholder="Masukkan LTV dalam Persen"
                  required />
                <x-input-error :messages="$errors->get('ltv')" class="mt-2" />
              </div>

              <!-- Uang Pinjaman (Rp) -->
              <div class="md:col-span-2">
                <x-input
                  id="uang_pinjaman_rp"
                  name="uang_pinjaman_rp"
                  label="Uang Pinjaman (Rp)"
                  type="number"
                  step="0.01"
                  min="0"
                  class="mt-1 block w-full"
                  :value="old('uang_pinjaman_rp')"
                  placeholder="Masukkan Uang Pinjaman dalam Rupiah"
                  required />
                <x-input-error :messages="$errors->get('uang_pinjaman_rp')" class="mt-2" />
              </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end space-x-4 pt-6">
              <a href="{{ route('hps-emas.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                <x-icon name="x" class="w-4 h-4 inline mr-1" />
                Batal
              </a>
              <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                <x-icon name="check" class="w-4 h-4 inline mr-1" />
                Simpan
              </button>
            </div>
          </form>
    </div>
  </x-card>
@endsection
