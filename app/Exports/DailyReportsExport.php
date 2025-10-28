<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyReportsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle {
    protected $reports;
    protected $date;

    public function __construct($reports, $date) {
        $this->reports = $reports;
        $this->date = $date;
    }

    public function collection() {
        return $this->reports;
    }

    public function headings(): array {
        return [
            'Branch',
            'Total Devices',
            'Total Detections',
            'Total Events',
            'Unique Persons',
            'Report Date',
        ];
    }

    public function map($report): array {
        return [
            $report->branch->branch_name ?? 'Overall',
            $report->total_devices,
            $report->total_detections,
            $report->total_events,
            $report->unique_person_count,
            \Carbon\Carbon::parse($report->report_date)->format('Y-m-d'),
        ];
    }

    public function styles(Worksheet $sheet) {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }

    public function title(): string {
        return 'Daily Report ' . $this->date;
    }
}
