<?php

namespace App\Services;

use App\Models\Shipment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ShipmentService
{
    /**
     * Create a shipment inside a transaction and return the model.
     *
     * @param array $data
     * @return Shipment
     */
    public function create(array $data): Shipment
    {
        return DB::transaction(function () use ($data) {
            return Shipment::create($data);
        });
    }

    /**
     * Paginate shipments with optional search and filters.
     * Supported filters: q (search), customer_id, status
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(array $filters = [], int $perPage = 10)
    {
        $query = Shipment::with('customer');

        if (!empty($filters['q'])) {
            $q = trim($filters['q']);
            $query->where(function ($w) use ($q) {
                $w->where('shipment_code', 'like', "%{$q}%")
                  ->orWhere('supplier_name', 'like', "%{$q}%")
                  ->orWhereHas('customer', function ($c) use ($q) {
                      $c->where('full_name', 'like', "%{$q}%");
                  });
            });
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $page = isset($filters['page']) ? (int) $filters['page'] : null;

        return $query->latest()->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Update an existing shipment and return the model.
     *
     * @param Shipment $shipment
     * @param array $data
     * @return Shipment
     */
    public function update(Shipment $shipment, array $data): Shipment
    {
        return DB::transaction(function () use ($shipment, $data) {
            $shipment->update($data);
            return $shipment->fresh();
        });
    }

    /**
     * Delete a shipment.
     *
     * @param Shipment|int $shipment
     * @return void
     */
    public function delete(Shipment|int $shipment): void
    {
        DB::transaction(function () use ($shipment) {
            if (is_int($shipment)) {
                $s = Shipment::find($shipment);
                if (! $s) {
                    throw new ModelNotFoundException("Shipment not found: {$shipment}");
                }
                $s->delete();
                return;
            }

            $shipment->delete();
        });
    }

    /**
     * Find a shipment by id, optionally with relations.
     *
     * @param int $id
     * @param array $with
     * @return Shipment|null
     */
    public function find(int $id, array $with = []): ?Shipment
    {
        $query = Shipment::query();
        if (!empty($with)) {
            $query->with($with);
        }

        return $query->find($id);
    }

    /**
     * Find a shipment or throw a ModelNotFoundException.
     *
     * @param int $id
     * @param array $with
     * @return Shipment
     */
    public function findOrFail(int $id, array $with = []): Shipment
    {
        $query = Shipment::query();
        if (!empty($with)) {
            $query->with($with);
        }

        return $query->findOrFail($id);
    }
}
