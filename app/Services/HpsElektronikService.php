<?php

namespace App\Services;

use App\Models\HpsElektronik;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HpsElektronikService extends BaseService
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->model = new HpsElektronik();
        $this->searchableFields = ['kdwilayah', 'jenis_barang', 'merek', 'barang', 'grade', 'kondisi'];
        $this->orderByColumn = 'created_at';
        $this->orderByDirection = 'desc';
    }

    /**
     * Find HPS Elektronik by ID
     *
     * @param int $id
     * @return HpsElektronik|null
     */
    public function findById(int $id): ?HpsElektronik
    {
        /** @var HpsElektronik|null */
        return parent::findById($id);
    }

    /**
     * Create new HPS Elektronik record
     *
     * @param array $data
     * @return HpsElektronik
     */
    public function createHpsElektronik(array $data): HpsElektronik
    {
        return DB::transaction(function () use ($data) {
            $hpsElektronik = $this->create($data);
            return $hpsElektronik;
        });
    }

    /**
     * Update HPS Elektronik record
     *
     * @param HpsElektronik $hpsElektronik
     * @param array $data
     * @return bool
     */
    public function updateHpsElektronik(HpsElektronik $hpsElektronik, array $data): bool
    {
        return $this->update($hpsElektronik, $data);
    }

    /**
     * Delete HPS Elektronik record
     *
     * @param HpsElektronik $hpsElektronik
     * @return bool
     */
    public function deleteHpsElektronik(HpsElektronik $hpsElektronik): bool
    {
        return $this->delete($hpsElektronik);
    }

    /**
     * Get HPS Elektronik by wilayah
     *
     * @param string $kdwilayah
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByWilayah(string $kdwilayah)
    {
        return HpsElektronik::where('kdwilayah', $kdwilayah)->active()->get();
    }

    /**
     * Get HPS Elektronik by jenis barang
     *
     * @param string $jenisBarang
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByJenisBarang(string $jenisBarang)
    {
        return HpsElektronik::where('jenis_barang', $jenisBarang)->active()->get();
    }

    /**
     * Get HPS Elektronik by merek
     *
     * @param string $merek
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByMerek(string $merek)
    {
        return HpsElektronik::where('merek', $merek)->active()->get();
    }

    /**
     * Get HPS Elektronik by grade
     *
     * @param string $grade
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByGrade(string $grade)
    {
        return HpsElektronik::where('grade', $grade)->active()->get();
    }

    /**
     * Get HPS Elektronik by tahun
     *
     * @param int $tahun
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByTahun(int $tahun)
    {
        return HpsElektronik::where('tahun', $tahun)->active()->get();
    }

    /**
     * Get HPS Elektronik by price range
     *
     * @param float $minPrice
     * @param float $maxPrice
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByPriceRange(float $minPrice, float $maxPrice)
    {
        return HpsElektronik::whereBetween('harga', [$minPrice, $maxPrice])->active()->get();
    }

    /**
     * Get statistics for HPS Elektronik
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $totalRecords = HpsElektronik::count();
        $activeRecords = HpsElektronik::where('active', true)->count();
        $inactiveRecords = HpsElektronik::where('active', false)->count();

        $avgPrice = HpsElektronik::where('active', true)->avg('harga');
        $minPrice = HpsElektronik::where('active', true)->min('harga');
        $maxPrice = HpsElektronik::where('active', true)->max('harga');

        $wilayahCount = HpsElektronik::distinct('kdwilayah')->count('kdwilayah');
        $jenisBarangCount = HpsElektronik::distinct('jenis_barang')->count('jenis_barang');
        $merekCount = HpsElektronik::distinct('merek')->count('merek');

        $topWilayah = HpsElektronik::select('kdwilayah')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('kdwilayah')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        $topJenisBarang = HpsElektronik::select('jenis_barang')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('jenis_barang')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        $topMerek = HpsElektronik::select('merek')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('merek')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        return [
            'total_items' => $totalRecords,
            'active_items' => $activeRecords,
            'inactive_items' => $inactiveRecords,
            'total_value' => HpsElektronik::sum('harga'),
            'average_value' => round($avgPrice, 2),
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'wilayah_count' => $wilayahCount,
            'jenis_barang_count' => $jenisBarangCount,
            'merek_count' => $merekCount,
            'top_wilayah' => $topWilayah,
            'top_jenis_barang' => $topJenisBarang,
            'top_merek' => $topMerek,
        ];
    }

    /**
     * Get all records with filters and search applied
     */
    public function getAllWithFilters(?string $search = null, array $filters = [])
    {
        $query = $this->getBaseQuery();

        // Apply search
        if (!empty($search)) {
            $query = $this->applySearch($query, $search);
        }

        // Apply filters
        if (!empty($filters)) {
            $query = $this->applyFilters($query, $filters);
        }

        return $query->orderBy($this->orderByColumn, $this->orderByDirection)->get();
    }

    /**
     * Get filter options for dropdowns
     *
     * @return array
     */
    public function getFilterOptions(): array
    {
        return [
            'wilayah' => HpsElektronik::distinct('kdwilayah')->pluck('kdwilayah', 'kdwilayah')->sort()->toArray(),
            'jenis_barang' => HpsElektronik::distinct('jenis_barang')->pluck('jenis_barang', 'jenis_barang')->sort()->toArray(),
            'merek' => HpsElektronik::distinct('merek')->pluck('merek', 'merek')->sort()->toArray(),
            'grade' => HpsElektronik::distinct('grade')->pluck('grade', 'grade')->sort()->toArray(),
            'tahun' => HpsElektronik::distinct('tahun')->pluck('tahun', 'tahun')->sort()->toArray(),
        ];
    }
}
