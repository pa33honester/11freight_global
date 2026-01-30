<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Paginate users with optional search filter.
     * Supported filters: q (search by name or email)
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(array $filters = [], int $perPage = 10)
    {
        $query = User::with('roles');

        if (!empty($filters['q'])) {
            $q = trim($filters['q']);
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $page = isset($filters['page']) ? (int) $filters['page'] : null;

        return $query->latest()->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Find a user by id.
     *
     * @param int $id
     * @return User
     */
    public function find(int $id): User
    {
        return User::findOrFail($id);
    }

    /**
     * Create a user inside a transaction and return the model.
     * Automatically hashes `password` if provided.
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            return User::create($data);
        });
    }

    /**
     * Update an existing user and return the fresh model.
     * Hashes `password` when present.
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $user->update($data);
            return $user->fresh();
        });
    }

    /**
     * Delete a user.
     *
     * @param User|int $user
     * @return void
     */
    public function delete(User|int $user): void
    {
        DB::transaction(function () use ($user) {
            if (is_int($user)) {
                $u = User::find($user);
                if (! $u) {
                    throw new ModelNotFoundException("User not found: {$user}");
                }
                $u->delete();
                return;
            }

            $user->delete();
        });
    }

    /**
     * Assign a role to a user.
     *
     * @param User|int $user
     * @param string $role
     * @return void
     */
    public function assignRole(User|int $user, string $role): void
    {
        $u = is_int($user) ? User::findOrFail($user) : $user;
        $u->assignRole($role);
    }

    /**
     * Sync roles for a user (replace existing roles).
     *
     * @param User|int $user
     * @param array $roles
     * @return void
     */
    public function syncRoles(User|int $user, array $roles): void
    {
        $u = is_int($user) ? User::findOrFail($user) : $user;
        $u->syncRoles($roles);
    }

    /**
     * Remove a single role from a user.
     *
     * @param User|int $user
     * @param string $role
     * @return void
     */
    public function removeRole(User|int $user, string $role): void
    {
        $u = is_int($user) ? User::findOrFail($user) : $user;
        $u->removeRole($role);
    }

    /**
     * Check if a user has a role.
     *
     * @param User|int $user
     * @param string $role
     * @return bool
     */
    public function hasRole(User|int $user, string $role): bool
    {
        $u = is_int($user) ? User::findOrFail($user) : $user;
        return $u->hasRole($role);
    }
}
