<?php

namespace App\Http\Controllers;

use App\Services\HpsEmasService;
use App\Services\BaseExportService;
use App\Models\HpsEmas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exports\HpsEmasExport;
use App\Exports\HpsEmasTemplateExport;
use App\Imports\HpsEmasImport;
use Maatwebsite\Excel\Facades\Excel;

class HpsEmasController extends Controller
{
    protected $hpsEmasService;
    protected $baseExportService;

    public function __construct(HpsEmasService $hpsEmasService, BaseExportService $baseExportService)
    {
        $this->hpsEmasService = $hpsEmasService;
        $this->baseExportService = $baseExportService;
    }

    /**
     * Display a listing of HPS Emas records
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');
        $filters = $request->only(['jenis_barang', 'kadar_karat', 'active']);

        // Remove empty filters
        $filters = array_filter($filters, function ($value) {
            return $value !== null && $value !== '';
        });

        $hpsEmas = $this->hpsEmasService->getPaginatedWithFilters($filters, $perPage);
        $perPageOptions = $this->hpsEmasService->getPerPageOptions();
        $filterOptions = $this->hpsEmasService->getFilterOptions();
        $statistics = $this->hpsEmasService->getStatistics();

        return view('hps-emas.index', compact(
            'hpsEmas',
            'perPageOptions',
            'search',
            'perPage',
            'filters',
            'filterOptions',
            'statistics'
        ));
    }

    /**
     * Show the form for creating a new HPS Emas record
     */
    public function create()
    {
        $filterOptions = $this->hpsEmasService->getFilterOptions();
        return view('hps-emas.create', compact('filterOptions'));
    }

    /**
     * Store a newly created HPS Emas record
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jenis_barang' => 'required|string|max:255',
            'stle_rp' => 'required|numeric|min:0',
            'kadar_karat' => 'required|integer|min:1|max:24',
            'berat_gram' => 'required|numeric|min:0',
            'nilai_taksiran_rp' => 'required|numeric|min:0',
            'ltv' => 'required|numeric|min:0|max:100',
            'uang_pinjaman_rp' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $this->hpsEmasService->createHpsEmas($request->all());
            return redirect()->route('hps-emas.index')
                ->with('success', 'HPS Emas berhasil dibuat.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal membuat HPS Emas: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified HPS Emas record
     */
    public function show(HpsEmas $hpsEmas)
    {
        return view('hps-emas.show', compact('hpsEmas'));
    }

    /**
     * Show the form for editing the specified HPS Emas record
     */
    public function edit(HpsEmas $hpsEmas)
    {
        $filterOptions = $this->hpsEmasService->getFilterOptions();
        return view('hps-emas.edit', compact('hpsEmas', 'filterOptions'));
    }

    /**
     * Update the specified HPS Emas record
     */
    public function update(Request $request, HpsEmas $hpsEmas)
    {
        $validator = Validator::make($request->all(), [
            'jenis_barang' => 'required|string|max:255',
            'stle_rp' => 'required|numeric|min:0',
            'kadar_karat' => 'required|integer|min:1|max:24',
            'berat_gram' => 'required|numeric|min:0',
            'nilai_taksiran_rp' => 'required|numeric|min:0',
            'ltv' => 'required|numeric|min:0|max:100',
            'uang_pinjaman_rp' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $this->hpsEmasService->updateHpsEmas($hpsEmas->id, $request->all());
            return redirect()->route('hps-emas.index')
                ->with('success', 'HPS Emas berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui HPS Emas: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified HPS Emas record
     */
    public function destroy(HpsEmas $hpsEmas)
    {
        try {
            $this->hpsEmasService->deleteHpsEmas($hpsEmas->id);
            return redirect()->route('hps-emas.index')
                ->with('success', 'HPS Emas berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus HPS Emas: ' . $e->getMessage());
        }
    }

    /**
     * Toggle active status
     */
    public function toggle(HpsEmas $hpsEmas)
    {
        try {
            $this->hpsEmasService->toggleActive($hpsEmas->id);
            $status = $hpsEmas->fresh()->active ? 'diaktifkan' : 'dinonaktifkan';
            return redirect()->route('hps-emas.index')
                ->with('success', "HPS Emas berhasil {$status}.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengubah status HPS Emas: ' . $e->getMessage());
        }
    }

    /**
     * Export HPS Emas data
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'excel');
        $filters = $request->only(['jenis_barang', 'kadar_karat', 'active']);

        // Remove empty filters
        $filters = array_filter($filters, function ($value) {
            return $value !== null && $value !== '';
        });

        $data = $this->hpsEmasService->getAllWithFilters($filters);
        $statistics = $this->hpsEmasService->getStatistics();
        $export = new HpsEmasExport($data, $filters);

        return $this->baseExportService->export(
            $format,
            $export,
            'hps-emas.export-pdf',
            ['hpsEmas' => $data, 'filters' => $filters, 'statistics' => $statistics],
            'hps-emas-' . date('Y-m-d-H-i-s')
        );
    }

    /**
     * Import form
     */
    public function importForm()
    {
        return view('hps-emas.import');
    }

    public function downloadTemplate()
    {
        return Excel::download(new HpsEmasTemplateExport(), 'template-hps-emas.xlsx');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        Excel::import(new HpsEmasImport(), $request->file('file'));
        return redirect()->route('hps-emas.index')->with('success', 'Import completed');
    }
}
