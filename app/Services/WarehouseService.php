<?php

namespace App\Services;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WarehouseService
{
    /**
     * Create a warehouse record inside a transaction and return the model.
     *
     * @param array $data
     * @return Warehouse
     */
    public function create(array $data): Warehouse
    {
        return DB::transaction(function () use ($data) {
            return Warehouse::create($data);
        });
    }

    /**
     * Paginate warehouse inventory with optional search and filters.
     * Supported filters: q (search across shipment code and shelf), shipment_id
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(array $filters = [], int $perPage = 10)
    {
        $query = Warehouse::with(['shipment', 'intakeBy']);

        if (!empty($filters['q'])) {
            $q = trim($filters['q']);
            $query->where(function ($w) use ($q) {
                $w->where('shelf', 'like', "%{$q}%")
                  ->orWhereHas('shipment', function ($s) use ($q) {
                      $s->where('shipment_code', 'like', "%{$q}%");
                  });
            });
        }

        if (!empty($filters['shipment_id'])) {
            $query->where('shipment_id', $filters['shipment_id']);
        }

        $page = isset($filters['page']) ? (int) $filters['page'] : null;

        return $query->latest()->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Find a warehouse record by id including relationships.
     *
     * @param int $id
     * @return Warehouse
     */
    public function find(int $id): Warehouse
    {
        return Warehouse::with(['shipment', 'intakeBy'])->findOrFail($id);
    }

    /**
     * Update an existing warehouse record and return the model.
     *
     * @param Warehouse $warehouse
     * @param array $data
     * @return Warehouse
     */
    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        return DB::transaction(function () use ($warehouse, $data) {
            $warehouse->update($data);
            return $warehouse->fresh();
        });
    }

    /**
     * Delete a warehouse record.
     *
     * @param Warehouse|int $warehouse
     * @return void
     */
    public function delete(Warehouse|int $warehouse): void
    {
        DB::transaction(function () use ($warehouse) {
            if (is_int($warehouse)) {
                $w = Warehouse::find($warehouse);
                if (! $w) {
                    throw new ModelNotFoundException("Warehouse record not found: {$warehouse}");
                }
                // Remove stored photo if exists
                if ($w->photo_path && Storage::disk('public')->exists($w->photo_path)) {
                    Storage::disk('public')->delete($w->photo_path);
                }
                $w->delete();
                return;
            }

            if ($warehouse->photo_path && Storage::disk('public')->exists($warehouse->photo_path)) {
                Storage::disk('public')->delete($warehouse->photo_path);
            }

            $warehouse->delete();
        });
    }
}
