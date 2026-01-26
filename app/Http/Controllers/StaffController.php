<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
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
        $roles = \Spatie\Permission\Models\Role::orderBy('name')->get();

        // expose role names array
        $user->role_names = $user->roles->pluck('name');

        return Inertia::render('Admin/Staff/Show', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    public function updateRoles(Request $request, $id)
    {
        $request->validate(['roles' => 'array']);
        $roles = $request->input('roles', []);

        $user = User::findOrFail($id);
        $user->syncRoles($roles);

        return redirect()->back()->with('success', 'Roles updated.');
    }
}
