<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

abstract class BaseService
{
    /**
     * The model instance
     *
     * @var Model
     */
    protected $model;

    /**
     * Fields to search in
     *
     * @var array
     */
    protected $searchableFields = [];

    /**
     * Default order by column
     *
     * @var string
     */
    protected $orderByColumn = 'created_at';

    /**
     * Default order direction
     *
     * @var string
     */
    protected $orderByDirection = 'desc';

    /**
     * Get paginated results with optional search
     *
     * @param string|null $search
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginate(?string $search = null, int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        // Validate per page value
        $perPage = $this->validatePerPage($perPage);

        $query = $this->getBaseQuery();

        // Apply filters
        if (!empty($filters)) {
            $query = $this->applyFilters($query, $filters);
        }

        // Apply search if provided
        if (!empty($search)) {
            $query = $this->applySearch($query, $search);
        }

        // Apply default ordering
        $query = $query->orderBy($this->orderByColumn, $this->orderByDirection);

        return $query->paginate($perPage);
    }

    /**
     * Search records with pagination
     *
     * @param string $search
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function search(string $search, int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        return $this->getPaginate($search, $perPage, $filters);
    }

    /**
     * Apply search to query
     *
     * @param Builder $query
     * @param string $search
     * @return Builder
     */
    protected function applySearch(Builder $query, string $search): Builder
    {
        if (empty($this->searchableFields)) {
            return $query;
        }

        $driver = $this->model->getConnection()->getDriverName();
        $likeOperator = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';

        return $query->where(function ($q) use ($search, $likeOperator) {
            foreach ($this->searchableFields as $field) {
                $q->orWhere($field, $likeOperator, "%{$search}%");
            }
        });
    }

    /**
     * Apply filters to query
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $field => $value) {
            if (!is_null($value) && $value !== '') {
                // Special handling for common filter types
                if ($field === 'date_from') {
                    $query->where('created_at', '>=', $value);
                    continue;
                }
                if ($field === 'date_to') {
                    $query->where('created_at', '<=', $value);
                    continue;
                }
                if ($field === 'processed') {
                    $query->where('processed', (bool) $value);
                    continue;
                }

                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
        }

        return $query;
    }

    /**
     * Build filters array from request based on allowed keys
     *
     * @param Request $request
     * @param array $allowedKeys
     * @return array
     */
    protected function buildFiltersFromRequest(Request $request, array $allowedKeys): array
    {
        $filters = [];

        foreach ($allowedKeys as $key) {
            if ($request->filled($key)) {
                $filters[$key] = $request->input($key);
            }
        }

        return $filters;
    }

    /**
     * Apply ordering to query with safe defaults
     *
     * @param Builder $query
     * @param string|null $orderBy
     * @param string|null $direction
     * @return Builder
     */
    protected function applyOrdering(Builder $query, ?string $orderBy = null, ?string $direction = null): Builder
    {
        $column = $orderBy ?: $this->orderByColumn;
        $dir = strtolower($direction ?: $this->orderByDirection);
        if (!in_array($dir, ['asc', 'desc'], true)) {
            $dir = $this->orderByDirection;
        }
        return $query->orderBy($column, $dir);
    }

    /**
     * Paginate using common request params (search, per_page, filters, ordering)
     *
     * @param Request $request
     * @param array $filterKeys
     * @return LengthAwarePaginator
     */
    public function paginateFromRequest(Request $request, array $filterKeys = []): LengthAwarePaginator
    {
        $perPage = $this->validatePerPage((int) $request->input('per_page', 10));
        $search = $request->input('search');
        $filters = $this->buildFiltersFromRequest($request, $filterKeys);
        $orderBy = $request->input('order_by');
        $direction = $request->input('order_dir');

        $query = $this->getBaseQuery();

        if (!empty($filters)) {
            $query = $this->applyFilters($query, $filters);
        }

        if (!empty($search)) {
            $query = $this->applySearch($query, $search);
        }

        $query = $this->applyOrdering($query, $orderBy, $direction);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Ensure model is initialized
     *
     * @throws \Exception
     */
    protected function ensureModelInitialized(): void
    {
        if (!$this->model) {
            throw new \Exception('Model not initialized in service constructor');
        }
    }

    /**
     * Get base query
     *
     * @return Builder
     */
    protected function getBaseQuery(): Builder
    {
        $this->ensureModelInitialized();
        return $this->model->query();
    }

    /**
     * Find record by ID
     *
     * @param int $id
     * @return Model|null
     */
    public function findById(int $id): ?Model
    {
        $this->ensureModelInitialized();
        return $this->model->find($id);
    }

    /**
     * Create new record
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        $this->ensureModelInitialized();
        return $this->model->create($data);
    }

    /**
     * Update record
     *
     * @param Model $model
     * @param array $data
     * @return bool
     */
    public function update(Model $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * Delete record
     *
     * @param Model $model
     * @return bool
     */
    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    /**
     * Get all records
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        $this->ensureModelInitialized();
        return $this->model->all();
    }

    /**
     * Validate and sanitize per page value
     *
     * @param int $perPage
     * @return int
     */
    protected function validatePerPage(int $perPage): int
    {
        $allowedPerPage = [10, 25, 50, 100];

        if (!in_array($perPage, $allowedPerPage)) {
            return 10; // Default fallback
        }

        return $perPage;
    }

    /**
     * Get available per page options
     *
     * @return array
     */
    public function getPerPageOptions(): array
    {
        return [10, 25, 50, 100];
    }
}

