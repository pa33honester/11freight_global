<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('name')->get();
        return Inertia::render('Admin/Roles/Index', ['roles' => $roles]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|unique:roles,name']);
        $role = Role::create(['name' => $request->input('name')]);

        if ($request->wantsJson() || $request->headers->get('accept') === 'application/json') {
            return response()->json($role, 201);
        }

        return redirect()->back()->with('success', 'Role created.');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        return redirect()->back()->with('success', 'Role deleted.');
    }
}
