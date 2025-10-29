<?php

namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class BaseExportService {
    /**
     * Export data to Excel
     *
     * @param mixed $exportClass Instance of export class (e.g., new EventLogsExport())
     * @param string $fileName File name without extension
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportToExcel($exportClass, string $fileName) {
        return Excel::download($exportClass, $fileName . '.xlsx');
    }

    /**
     * Export data to PDF
     *
     * @param string $viewName Blade view name (e.g., 'event-logs.export-pdf')
     * @param array $data Data to pass to view
     * @param string $fileName File name without extension
     * @param array $options PDF options (orientation, paper size, etc.)
     * @return \Illuminate\Http\Response
     */
    public function exportToPdf(string $viewName, array $data, string $fileName, array $options = []) {
        $pdf = Pdf::loadView($viewName, $data);

        // Apply options if provided
        if (isset($options['orientation'])) {
            $pdf->setPaper('a4', $options['orientation']);
        }

        if (isset($options['paper'])) {
            $pdf->setPaper($options['paper'], $options['orientation'] ?? 'portrait');
        }

        return $pdf->download($fileName . '.pdf');
    }

    /**
     * Export data based on format (excel or pdf)
     *
     * @param string $format 'excel' or 'pdf'
     * @param mixed $exportClass Export class instance (for Excel)
     * @param string $viewName Blade view name (for PDF)
     * @param array $data Data to pass to PDF view
     * @param string $fileName File name without extension
     * @param array $pdfOptions PDF options
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\Response
     */
    public function export(
        string $format,
        $exportClass,
        string $viewName,
        array $data,
        string $fileName,
        array $pdfOptions = []
    ) {
        if ($format === 'pdf') {
            return $this->exportToPdf($viewName, $data, $fileName, $pdfOptions);
        }

        // Default: Excel
        // If exportClass is a string (class name), instantiate it with data
        if (is_string($exportClass)) {
            $exportClass = new $exportClass($data);
        }
        return $this->exportToExcel($exportClass, $fileName);
    }

    /**
     * Generate dynamic filename with timestamp
     *
     * @param string $prefix File prefix (e.g., 'Event_Logs', 'Person_Tracking')
     * @param string $format 'Y-m-d_His' or custom format
     * @return string
     */
    public function generateFileName(string $prefix, string $format = 'Y-m-d_His'): string {
        return $prefix . '_' . now()->format($format);
    }

    /**
     * Build filters array from request
     *
     * @param \Illuminate\Http\Request $request
     * @param array $filterKeys Array of filter key names
     * @return array
     */
    public function buildFilters($request, array $filterKeys): array {
        $filters = [];

        foreach ($filterKeys as $key) {
            if ($request->filled($key)) {
                $filters[$key] = $request->input($key);
            }
        }

        return $filters;
    }
}
