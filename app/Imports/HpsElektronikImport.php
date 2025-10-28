<?php

namespace App\Imports;

use App\Models\HpsElektronik;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class HpsElektronikImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return new HpsElektronik([
            'kdwilayah' => $row['kdwilayah'] ?? null,
            'jenis_barang' => $row['jenis_barang'] ?? null,
            'merek' => $row['merek'] ?? null,
            'barang' => $row['barang'] ?? null,
            'tahun' => isset($row['tahun']) ? (int) $row['tahun'] : null,
            'harga' => isset($row['harga']) ? (float) $row['harga'] : null,
            'active' => isset($row['active']) ? (bool) $row['active'] : true,
            'grade' => $row['grade'] ?? null,
            'kondisi' => $row['kondisi'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            '*.kdwilayah' => ['required', 'string'],
            '*.jenis_barang' => ['required', 'string'],
            '*.merek' => ['nullable', 'string'],
            '*.barang' => ['nullable', 'string'],
            '*.tahun' => ['nullable', 'integer'],
            '*.harga' => ['nullable', 'numeric', 'min:0'],
            '*.active' => ['nullable', 'in:0,1'],
            '*.grade' => ['nullable', 'string'],
            '*.kondisi' => ['nullable', 'string'],
        ];
    }
}


