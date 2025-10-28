<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class HpsEmasTemplateExport implements FromArray, WithHeadings, WithTitle
{
    public function array(): array
    {
        return [
            ['CINCIN', 950000, 24, 5.5, 920000, 80.0, 736000, 1],
        ];
    }

    public function headings(): array
    {
        return [
            'jenis_barang',
            'stle_rp',
            'kadar_karat',
            'berat_gram',
            'nilai_taksiran_rp',
            'ltv',
            'uang_pinjaman_rp',
            'active',
        ];
    }

    public function title(): string
    {
        return 'Template HPS Emas';
    }
}


