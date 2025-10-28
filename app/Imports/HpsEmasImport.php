<?php

namespace App\Imports;

use App\Models\HpsEmas;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class HpsEmasImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return new HpsEmas([
            'jenis_barang' => $row['jenis_barang'] ?? null,
            'stle_rp' => isset($row['stle_rp']) ? (float) $row['stle_rp'] : null,
            'kadar_karat' => isset($row['kadar_karat']) ? (int) $row['kadar_karat'] : null,
            'berat_gram' => isset($row['berat_gram']) ? (float) $row['berat_gram'] : null,
            'nilai_taksiran_rp' => isset($row['nilai_taksiran_rp']) ? (float) $row['nilai_taksiran_rp'] : null,
            'ltv' => isset($row['ltv']) ? (float) $row['ltv'] : null,
            'uang_pinjaman_rp' => isset($row['uang_pinjaman_rp']) ? (float) $row['uang_pinjaman_rp'] : null,
            'active' => isset($row['active']) ? (bool) $row['active'] : true,
        ]);
    }

    public function rules(): array
    {
        return [
            '*.jenis_barang' => ['required', 'string'],
            '*.stle_rp' => ['nullable', 'numeric', 'min:0'],
            '*.kadar_karat' => ['nullable', 'integer', 'min:1', 'max:24'],
            '*.berat_gram' => ['nullable', 'numeric', 'min:0'],
            '*.nilai_taksiran_rp' => ['nullable', 'numeric', 'min:0'],
            '*.ltv' => ['nullable', 'numeric', 'min:0', 'max:100'],
            '*.uang_pinjaman_rp' => ['nullable', 'numeric', 'min:0'],
            '*.active' => ['nullable', 'in:0,1'],
        ];
    }
}


