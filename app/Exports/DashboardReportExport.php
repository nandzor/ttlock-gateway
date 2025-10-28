<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DashboardReportExport implements WithMultipleSheets {
    protected $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function sheets(): array {
        return [
            new DashboardSummarySheet($this->data),
            new DailyTrendSheet($this->data['dailyTrend']),
            new TopBranchesSheet($this->data['topBranches']),
        ];
    }
}

class DashboardSummarySheet implements FromCollection, WithHeadings, WithStyles, WithTitle {
    protected $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function collection() {
        return collect([
            [
                'Total Detections',
                $this->data['totalDetections'],
            ],
            [
                'Unique Persons',
                $this->data['uniquePersons'],
            ],
            [
                'Active Branches',
                $this->data['uniqueBranches'],
            ],
            [
                'Active Devices',
                $this->data['uniqueDevices'],
            ],
        ]);
    }

    public function headings(): array {
        return ['Metric', 'Value'];
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
        return 'Summary Statistics';
    }
}

class DailyTrendSheet implements FromCollection, WithHeadings, WithStyles, WithTitle {
    protected $dailyTrend;

    public function __construct($dailyTrend) {
        $this->dailyTrend = $dailyTrend;
    }

    public function collection() {
        return $this->dailyTrend->map(function ($item) {
            return [
                \Carbon\Carbon::parse($item->date)->format('Y-m-d'),
                $item->count,
            ];
        });
    }

    public function headings(): array {
        return ['Date', 'Detections'];
    }

    public function styles(Worksheet $sheet) {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '10B981'],
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }

    public function title(): string {
        return 'Daily Trend';
    }
}

class TopBranchesSheet implements FromCollection, WithHeadings, WithStyles, WithTitle {
    protected $topBranches;

    public function __construct($topBranches) {
        $this->topBranches = $topBranches;
    }

    public function collection() {
        return $this->topBranches->map(function ($item) {
            return [
                $item->branch->branch_name ?? 'N/A',
                $item->branch->city_name ?? 'N/A',
                $item->detection_count,
            ];
        });
    }

    public function headings(): array {
        return ['Branch Name', 'City', 'Detection Count'];
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
        return 'Top Branches';
    }
}
