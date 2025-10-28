<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonthlyReportsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle {
    protected $reports;
    protected $month;

    public function __construct($reports, $month) {
        $this->reports = $reports;
        $this->month = $month;
    }

    public function collection() {
        return $this->reports;
    }

    public function headings(): array {
        return [
            'Date',
            'Day',
            'Branch',
            'City',
            'Total Devices',
            'Total Detections',
            'Total Events',
            'Unique Persons',
            'Avg/Day',
        ];
    }

    public function map($report): array {
        return [
            \Carbon\Carbon::parse($report->report_date)->format('Y-m-d'),
            \Carbon\Carbon::parse($report->report_date)->format('l'),
            $report->branch->branch_name ?? 'Overall',
            $report->branch->city ?? 'N/A',
            $report->total_devices,
            $report->total_detections,
            $report->total_events,
            $report->unique_person_count,
            number_format($report->total_detections / max($report->total_devices, 1), 1),
        ];
    }

    public function styles(Worksheet $sheet) {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F59E0B'],
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }

    public function title(): string {
        return 'Monthly Report ' . $this->month;
    }
}
