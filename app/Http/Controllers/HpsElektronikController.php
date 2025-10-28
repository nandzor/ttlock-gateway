<?php

namespace App\Http\Controllers;

use App\Services\HpsElektronikService;
use App\Services\BaseExportService;
use App\Models\HpsElektronik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exports\HpsElektronikExport;
use App\Exports\HpsElektronikTemplateExport;
use App\Imports\HpsElektronikImport;
use Maatwebsite\Excel\Facades\Excel;

class HpsElektronikController extends Controller
{
    protected $hpsElektronikService;
    protected $baseExportService;

    public function __construct(HpsElektronikService $hpsElektronikService, BaseExportService $baseExportService)
    {
        $this->hpsElektronikService = $hpsElektronikService;
        $this->baseExportService = $baseExportService;
    }

    /**
     * Display a listing of HPS Elektronik records
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');
        $filters = $request->only(['kdwilayah', 'jenis_barang', 'merek', 'grade', 'tahun', 'active']);

        // Remove empty filters
        $filters = array_filter($filters, function ($value) {
            return $value !== null && $value !== '';
        });

        $hpsElektronik = $this->hpsElektronikService->getPaginate($search, $perPage, $filters);
        $perPageOptions = $this->hpsElektronikService->getPerPageOptions();
        $filterOptions = $this->hpsElektronikService->getFilterOptions();
        $statistics = $this->hpsElektronikService->getStatistics();

        return view('hps-elektronik.index', compact(
            'hpsElektronik',
            'perPageOptions',
            'search',
            'perPage',
            'filters',
            'filterOptions',
            'statistics'
        ));
    }

    /**
     * Show the form for creating a new HPS Elektronik record
     */
    public function create()
    {
        $filterOptions = $this->hpsElektronikService->getFilterOptions();
        return view('hps-elektronik.create', compact('filterOptions'));
    }

    /**
     * Show import form
     */
    public function importForm()
    {
        return view('hps-elektronik.import');
    }

    /**
     * Download Excel template
     */
    public function downloadTemplate()
    {
        return Excel::download(new HpsElektronikTemplateExport(), 'template-hps-elektronik.xlsx');
    }

    /**
     * Handle import
     */
    public function importStore(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        Excel::import(new HpsElektronikImport(), $request->file('file'));

        return redirect()->route('hps-elektronik.index')->with('success', 'Import completed');
    }

    /**
     * Store a newly created HPS Elektronik record
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kdwilayah' => 'required|string|max:255',
            'jenis_barang' => 'required|string|max:255',
            'merek' => 'required|string|max:255',
            'barang' => 'required|string|max:255',
            'tahun' => 'required|integer|min:1900|max:' . date('Y'),
            'harga' => 'required|numeric|min:0',
            'active' => 'boolean',
            'grade' => 'nullable|string|max:10',
            'kondisi' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['active'] = $request->has('active') ? true : false;

        $this->hpsElektronikService->createHpsElektronik($data);

        return redirect()->route('hps-elektronik.index')
            ->with('success', 'HPS Elektronik record created successfully.');
    }

    /**
     * Display the specified HPS Elektronik record
     */
    public function show($id)
    {
        $hpsElektronik = $this->hpsElektronikService->findById($id);

        if (!$hpsElektronik) {
            return redirect()->route('hps-elektronik.index')
                ->with('error', 'HPS Elektronik record not found.');
        }

        return view('hps-elektronik.show', compact('hpsElektronik'));
    }

    /**
     * Show the form for editing the specified HPS Elektronik record
     */
    public function edit($id)
    {
        $hpsElektronik = $this->hpsElektronikService->findById($id);

        if (!$hpsElektronik) {
            return redirect()->route('hps-elektronik.index')
                ->with('error', 'HPS Elektronik record not found.');
        }

        $filterOptions = $this->hpsElektronikService->getFilterOptions();
        return view('hps-elektronik.edit', compact('hpsElektronik', 'filterOptions'));
    }

    /**
     * Update the specified HPS Elektronik record
     */
    public function update(Request $request, $id)
    {
        $hpsElektronik = $this->hpsElektronikService->findById($id);

        if (!$hpsElektronik) {
            return redirect()->route('hps-elektronik.index')
                ->with('error', 'HPS Elektronik record not found.');
        }

        $validator = Validator::make($request->all(), [
            'kdwilayah' => 'required|string|max:255',
            'jenis_barang' => 'required|string|max:255',
            'merek' => 'required|string|max:255',
            'barang' => 'required|string|max:255',
            'tahun' => 'required|integer|min:1900|max:' . date('Y'),
            'harga' => 'required|numeric|min:0',
            'active' => 'boolean',
            'grade' => 'nullable|string|max:10',
            'kondisi' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['active'] = $request->has('active') ? true : false;

        $this->hpsElektronikService->updateHpsElektronik($hpsElektronik, $data);

        return redirect()->route('hps-elektronik.index')
            ->with('success', 'HPS Elektronik record updated successfully.');
    }

    /**
     * Remove the specified HPS Elektronik record
     */
    public function destroy($id)
    {
        $hpsElektronik = $this->hpsElektronikService->findById($id);

        if (!$hpsElektronik) {
            return redirect()->route('hps-elektronik.index')
                ->with('error', 'HPS Elektronik record not found.');
        }

        $this->hpsElektronikService->deleteHpsElektronik($hpsElektronik);

        return redirect()->route('hps-elektronik.index')
            ->with('success', 'HPS Elektronik record deleted successfully.');
    }

    /**
     * Toggle active status
     */
    public function toggle($id)
    {
        $hpsElektronik = $this->hpsElektronikService->findById($id);

        if (!$hpsElektronik) {
            return redirect()->route('hps-elektronik.index')
                ->with('error', 'HPS Elektronik record not found.');
        }

        $hpsElektronik->update(['active' => !$hpsElektronik->active]);

        $status = $hpsElektronik->active ? 'activated' : 'deactivated';
        return redirect()->route('hps-elektronik.index')
            ->with('success', "HPS Elektronik record {$status} successfully.");
    }

    /**
     * Export HPS Elektronik data
     */
    public function export(Request $request)
    {
        $search = $request->get('search');
        $format = $request->get('format', 'excel'); // Default to excel, can be 'pdf' or 'excel'

        // Build filters using BaseExportService
        $filterKeys = ['kdwilayah', 'jenis_barang', 'merek', 'grade', 'tahun', 'active'];
        $filters = $this->baseExportService->buildFilters($request, $filterKeys);

        // Get data using service
        $hpsElektronik = $this->hpsElektronikService->getAllWithFilters($search, $filters);

        // Generate filename using BaseExportService
        $fileName = $this->baseExportService->generateFileName('HPS_Elektronik');

        // Create export instance
        $exportClass = new HpsElektronikExport($hpsElektronik, $filters);

        if ($format === 'pdf') {
            // For PDF export, we need a view
            $data = [
                'hpsElektronik' => $hpsElektronik,
                'filters' => $filters,
                'search' => $search,
                'exportDate' => now()->format('Y-m-d H:i:s'),
            ];

            return $this->baseExportService->export(
                'pdf',
                $exportClass,
                'hps-elektronik.export-pdf',
                $data,
                $fileName,
                ['orientation' => 'landscape']
            );
        }

        // Default: Excel export
        return $this->baseExportService->exportToExcel($exportClass, $fileName);
    }
}
