<?php

namespace App\Services;

use App\Models\HpsEmas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HpsEmasService extends BaseService
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->model = new HpsEmas();
        $this->searchableFields = ['jenis_barang'];
        $this->orderByColumn = 'created_at';
        $this->orderByDirection = 'desc';
    }

    /**
     * Find HPS Emas by ID
     *
     * @param int $id
     * @return HpsEmas|null
     */
    public function findById(int $id): ?HpsEmas
    {
        /** @var HpsEmas|null */
        return parent::findById($id);
    }

    /**
     * Create new HPS Emas record
     *
     * @param array $data
     * @return HpsEmas
     */
    public function createHpsEmas(array $data): HpsEmas
    {
        return DB::transaction(function () use ($data) {
            $hpsEmas = $this->create($data);
            return $hpsEmas;
        });
    }

    /**
     * Update HPS Emas record
     *
     * @param int $id
     * @param array $data
     * @return HpsEmas
     */
    public function updateHpsEmas(int $id, array $data): HpsEmas
    {
        return DB::transaction(function () use ($id, $data) {
            $hpsEmas = $this->findById($id);
            if (!$hpsEmas) {
                throw new \Exception('HPS Emas not found');
            }
            $this->update($hpsEmas, $data);
            return $hpsEmas->fresh();
        });
    }

    /**
     * Delete HPS Emas record
     *
     * @param int $id
     * @return bool
     */
    public function deleteHpsEmas(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $hpsEmas = $this->findById($id);
            if (!$hpsEmas) {
                throw new \Exception('HPS Emas not found');
            }
            return $this->delete($hpsEmas);
        });
    }

    /**
     * Toggle active status
     *
     * @param int $id
     * @return HpsEmas
     */
    public function toggleActive(int $id): HpsEmas
    {
        return DB::transaction(function () use ($id) {
            $hpsEmas = $this->findById($id);
            if (!$hpsEmas) {
                throw new \Exception('HPS Emas not found');
            }

            $hpsEmas->update(['active' => !$hpsEmas->active]);
            return $hpsEmas;
        });
    }

    /**
     * Get all HPS Emas with filters
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllWithFilters(array $filters = [])
    {
        $query = $this->getBaseQuery();

        // Apply search
        if (!empty($filters['search'])) {
            $query = $this->applySearch($query, $filters['search']);
        }

        // Apply filters
        $query = $this->applyFilters($query, $filters);

        return $query->orderBy($this->orderByColumn, $this->orderByDirection)->get();
    }

    /**
     * Get paginated HPS Emas with filters
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginatedWithFilters(array $filters = [], int $perPage = 15)
    {
        $query = $this->getBaseQuery();

        // Apply search
        if (!empty($filters['search'])) {
            $query = $this->applySearch($query, $filters['search']);
        }

        // Apply filters
        $query = $this->applyFilters($query, $filters);

        return $query->orderBy($this->orderByColumn, $this->orderByDirection)->paginate($perPage);
    }

    /**
     * Get statistics for dashboard
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $totalItems = HpsEmas::count();
        $activeItems = HpsEmas::where('active', true)->count();
        $totalValue = HpsEmas::sum('nilai_taksiran_rp');
        $averageValue = $totalItems > 0 ? $totalValue / $totalItems : 0;

        return [
            'total_items' => $totalItems,
            'active_items' => $activeItems,
            'total_value' => $totalValue,
            'average_value' => $averageValue,
        ];
    }

    /**
     * Get filter options for dropdowns
     *
     * @return array
     */
    public function getFilterOptions(): array
    {
        return [
            'jenis_barang' => HpsEmas::distinct('jenis_barang')->pluck('jenis_barang', 'jenis_barang')->sort()->toArray(),
            'kadar_karat' => HpsEmas::distinct('kadar_karat')->pluck('kadar_karat', 'kadar_karat')->sort()->toArray(),
        ];
    }

    /**
     * Apply filters to query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyFilters(\Illuminate\Database\Eloquent\Builder $query, array $filters): \Illuminate\Database\Eloquent\Builder
    {
        if (!empty($filters['jenis_barang'])) {
            $query->where('jenis_barang', $filters['jenis_barang']);
        }

        if (!empty($filters['kadar_karat'])) {
            $query->where('kadar_karat', $filters['kadar_karat']);
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        return $query;
    }
}
