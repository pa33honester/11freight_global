<?php

namespace App\Http\Controllers;

use App\Models\Container;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContainerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $containers = Container::latest()->paginate(10);

        return Inertia::render('Containers/Index', [
            'containers' => $containers,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Containers/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'container_code' => 'required|string|max:50|unique:containers,container_code',
            'status' => 'nullable|in:OPEN,SEALED,IN_TRANSIT,ARRIVED',
            'departure_date' => 'nullable|date',
            'arrival_date' => 'nullable|date',
        ]);

        Container::create($validated);

        return redirect()->route('containers.index')->with('success', 'Container created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $container = Container::findOrFail($id);
        return Inertia::render('Containers/Show', [
            'container' => $container,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $container = Container::findOrFail($id);
        return Inertia::render('Containers/Edit', [
            'container' => $container,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $container = Container::findOrFail($id);

        $validated = $request->validate([
            'container_code' => 'required|string|max:50|unique:containers,container_code,' . $container->id,
            'status' => 'nullable|in:OPEN,SEALED,IN_TRANSIT,ARRIVED',
            'departure_date' => 'nullable|date',
            'arrival_date' => 'nullable|date',
        ]);

        $container->update($validated);

        return redirect()->route('containers.index')->with('success', 'Container updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $container = Container::findOrFail($id);
        $container->delete();

        return redirect()->route('containers.index')->with('success', 'Container deleted successfully.');
    }
}
