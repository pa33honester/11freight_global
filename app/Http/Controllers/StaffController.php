<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Schema;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        // Use spatie roles relationship (eager load roles)
        $users = User::with('roles')->select(['id', 'name', 'email', 'created_at'])->latest()->paginate(20)->withQueryString();

        // Map to include a `role` attribute (comma-separated role names)
        $users->getCollection()->transform(function ($u) {
            $u->role = $u->roles->pluck('name')->join(', ');
            return $u;
        });

        return Inertia::render('Admin/Staff/Index', [
            'users' => $users,
        ]);
    }

    public function show($id)
    {
        $user = User::with('roles')->findOrFail($id);
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

    public function updateRoles(Request $request, $id)
    {
        $request->validate([
            'roles' => 'required|array|size:1',
            'roles.*' => 'string|exists:roles,name',
        ]);
        $roles = $request->input('roles', []);

        $user = User::findOrFail($id);
        $user->syncRoles($roles);

        return redirect()->back()->with('success', 'Roles updated.');
    }
}
