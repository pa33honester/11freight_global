<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Container;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ContainerService
{
    /**
     * Create a container inside a transaction.
     *
     * @param array $data
     * @return Container
     */
    public function create(array $data): Container
    {
        return DB::transaction(function () use ($data) {
            return Container::create($data);
        });
    }

    /**
     * Update a container inside a transaction.
     *
     * @param Container $container
     * @param array $data
     * @return Container
     */
    public function update(Container $container, array $data): Container
    {
        return DB::transaction(function () use ($container, $data) {
            $container->update($data);
            return $container->fresh();
        });
    }

    /**
     * Paginate latest containers.
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 10)
    {
        return Container::latest()->paginate($perPage);
    }

    /**
     * Find a container by id.
     *
     * @param int $id
     * @param array $with
     * @return Container|null
     */
    public function find(int $id, array $with = []): ?Container
    {
        $query = Container::query();
        if (!empty($with)) {
            $query->with($with);
        }

        return $query->find($id);
    }

    /**
     * Find a container or fail.
     *
     * @param int $id
     * @param array $with
     * @return Container
     */
    public function findOrFail(int $id, array $with = []): Container
    {
        $query = Container::query();
        if (!empty($with)) {
            $query->with($with);
        }

        return $query->findOrFail($id);
    }

    /**
     * Delete a container.
     *
     * @param Container|int $container
     * @return void
     */
    public function delete(Container|int $container): void
    {
        DB::transaction(function () use ($container) {
            if (is_int($container)) {
                $c = Container::find($container);
                if (! $c) {
                    throw new ModelNotFoundException("Container not found: {$container}");
                }
                $c->delete();
                return;
            }

            $container->delete();
        });
    }

    /**
     * Return the first available container (status != DEPARTED).
     *
     * @return Container|null
     */
    public function firstAvailable(): ?Container
    {
        return Container::query()->where('status', '!=', 'DEPARTED')->first();
    }
}
