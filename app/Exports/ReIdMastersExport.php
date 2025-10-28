<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReIdMastersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle {
    protected $persons;
    protected $filters;

    public function __construct($persons, $filters = []) {
        $this->persons = $persons;
        $this->filters = $filters;
    }

    public function collection() {
        return $this->persons;
    }

    public function headings(): array {
        return [
            'Re-ID',
            'Person Name',
            'Detection Date',
            'First Detected',
            'Last Detected',
            'Total Branches',
            'Total Detections',
            'Status',
        ];
    }

    public function map($person): array {
        return [
            $person->re_id,
            $person->person_name ?: 'Unknown',
            \Carbon\Carbon::parse($person->detection_date)->format('Y-m-d'),
            $person->first_detected_at ? \Carbon\Carbon::parse($person->first_detected_at)->format('Y-m-d H:i:s') : 'N/A',
            $person->last_detected_at ? \Carbon\Carbon::parse($person->last_detected_at)->format('Y-m-d H:i:s') : 'N/A',
            $person->total_detection_branch_count,
            $person->total_actual_count,
            ucfirst($person->status),
        ];
    }

    public function styles(Worksheet $sheet) {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '9333EA'],
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }

    public function title(): string {
        return 'Person Tracking Export';
    }
}
