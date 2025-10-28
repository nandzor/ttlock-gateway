<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HpsElektronikExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $hpsElektronik;
    protected $filters;

    public function __construct($hpsElektronik, $filters = [])
    {
        $this->hpsElektronik = $hpsElektronik;
        $this->filters = $filters;
    }

    public function collection()
    {
        return $this->hpsElektronik;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kode Wilayah',
            'Jenis Barang',
            'Merek',
            'Barang',
            'Tahun',
            'Harga (Rp)',
            'Status',
            'Grade',
            'Kondisi',
            'Tanggal Dibuat',
            'Tanggal Diperbarui',
        ];
    }

    public function map($item): array
    {
        return [
            $item->id,
            $item->kdwilayah,
            $item->jenis_barang,
            $item->merek,
            $item->barang,
            $item->tahun,
            number_format($item->harga, 0, ',', '.'),
            $item->active ? 'Aktif' : 'Tidak Aktif',
            $item->grade,
            $item->kondisi,
            $item->created_at->format('Y-m-d H:i:s'),
            $item->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DC2626'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'HPS Elektronik Export';
    }
}
