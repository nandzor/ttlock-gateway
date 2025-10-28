<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HpsEmasExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $hpsEmas;
    protected $filters;

    public function __construct($hpsEmas, $filters = [])
    {
        $this->hpsEmas = $hpsEmas;
        $this->filters = $filters;
    }

    public function collection()
    {
        return $this->hpsEmas;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Jenis Barang',
            'STLE (Rp)',
            'Kadar Karat',
            'Berat (Gram)',
            'Nilai Taksiran (Rp)',
            'LTV (%)',
            'Uang Pinjaman (Rp)',
            'Status',
            'Tanggal Dibuat',
            'Tanggal Diperbarui',
        ];
    }

    public function map($item): array
    {
        return [
            $item->id,
            $item->jenis_barang,
            number_format($item->stle_rp, 0, ',', '.'),
            $item->kadar_karat,
            number_format($item->berat_gram, 2, ',', '.'),
            number_format($item->nilai_taksiran_rp, 0, ',', '.'),
            number_format($item->ltv, 2, ',', '.'),
            number_format($item->uang_pinjaman_rp, 0, ',', '.'),
            $item->active ? 'Active' : 'Inactive',
            $item->created_at->format('d/m/Y H:i:s'),
            $item->updated_at->format('d/m/Y H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'E3F2FD',
                    ],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
            // Data rows
            'A:K' => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                ],
            ],
            // Number columns
            'C:C' => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],
            ],
            'E:E' => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],
            ],
            'F:F' => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],
            ],
            'G:G' => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],
            ],
            'H:H' => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'HPS Emas';
    }
}
