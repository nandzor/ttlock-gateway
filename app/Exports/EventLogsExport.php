<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EventLogsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle {
    protected $events;
    protected $filters;

    public function __construct($events, $filters = []) {
        $this->events = $events;
        $this->filters = $filters;
    }

    public function collection() {
        return $this->events;
    }

    public function headings(): array {
        return [
            'Event Type',
            'Branch',
            'Device',
            'Re-ID',
            'Detected Count',
            'Event Timestamp',
            'Image Sent',
            'Message Sent',
            'Notification Sent',
        ];
    }

    public function map($event): array {
        return [
            ucfirst($event->event_type),
            $event->branch->branch_name ?? 'N/A',
            $event->device->device_name ?? 'N/A',
            $event->re_id ?? 'N/A',
            $event->detected_count,
            \Carbon\Carbon::parse($event->event_timestamp)->format('Y-m-d H:i:s'),
            $event->image_sent ? 'Yes' : 'No',
            $event->message_sent ? 'Yes' : 'No',
            $event->notification_sent ? 'Yes' : 'No',
        ];
    }

    public function styles(Worksheet $sheet) {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DC2626'],
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }

    public function title(): string {
        return 'Event Logs Export';
    }
}
