<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class HpsElektronikTemplateExport implements FromArray, WithHeadings, WithTitle
{
    public function array(): array
    {
        // Provide one sample row (optional)
        return [
            ['JAKARTA', 'HANDPHONE', 'SAMSUNG', 'GALAXY A15', 2024, 2500000, 1, 'A', 'FULLSET LIKE NEW'],
        ];
    }

    public function headings(): array
    {
        return [
            'kdwilayah',
            'jenis_barang',
            'merek',
            'barang',
            'tahun',
            'harga',
            'active',
            'grade',
            'kondisi',
        ];
    }

    public function title(): string
    {
        return 'Template HPS Elektronik';
    }
}


