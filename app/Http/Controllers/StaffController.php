<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    public function index(Request $request, UserService $userService)
    {
        $filters = [
            'q' => $request->query('q'),
            'page' => $request->query('page'),
        ];

        $perPage = (int) $request->query('per_page', 20);

        $users = $userService->paginate($filters, $perPage);

        // Convert paginator to array and add a `role` field computed from loaded roles
        $usersArray = json_decode(json_encode($users), true);
        foreach ($usersArray['data'] as &$u) {
            $roleNames = [];
            if (!empty($u['roles']) && is_array($u['roles'])) {
                $roleNames = array_map(fn($r) => $r['name'] ?? '', $u['roles']);
            }
            $u['role'] = trim(implode(', ', array_filter($roleNames)));
            unset($u['roles']);
        }

        return Inertia::render('Admin/Staff/Index', [
            'users' => $usersArray,
            'filters' => [
                'q' => $request->query('q'),
                'per_page' => $perPage,
            ],
        ]);
    }

    public function show($id, UserService $userService)
    {
        $user = $userService->find((int) $id)->load('roles');
        // Ensure the expected canonical roles are listed and ordered for the UI
        $expected = ['admin', 'warehouse_staff', 'operation_manager', 'finance_manager'];

        // Ensure canonical roles exist in DB so admin can assign them.
        foreach ($expected as $rname) {
            Role::firstOrCreate(['name' => $rname]);
        }

        // Fetch all roles and order them by our expected canonical list first,
        // then any additional roles alphabetically after.
        $all = Role::orderBy('name')->get()
            ->map(fn($r) => ['id' => $r->id, 'name' => $r->name])
            ->values();

        $roles = $all->sortBy(function ($r) use ($expected) {
            $idx = array_search($r['name'], $expected, true);
            return $idx === false ? count($expected) + 1 : $idx;
        })->values();

        // expose role names array
        $user->role_names = $user->roles->pluck('name');

        return Inertia::render('Admin/Staff/Show', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    public function updateRoles(Request $request, $id, UserService $userService)
    {
        $request->validate([
            'roles' => 'required|array|size:1',
            'roles.*' => 'string|exists:roles,name',
        ]);
        $roles = $request->input('roles', []);

        $userService->syncRoles((int) $id, $roles);

        return redirect()->back()->with('success', 'Roles updated.');
    }
}
