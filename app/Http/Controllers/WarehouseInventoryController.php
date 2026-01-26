<?php

namespace App\Http\Controllers;

use App\Models\WarehouseInventory;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class WarehouseInventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = WarehouseInventory::with('shipment','intakeBy')->latest()->paginate(10);
        return Inertia::render('WarehouseInventory/Index', [
            'items' => $items,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $shipments = Shipment::orderBy('shipment_code')->get();
        return Inertia::render('WarehouseInventory/Create', [
            'shipments' => $shipments,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shipment_id' => 'required|exists:shipments,id',
            'shelf' => 'nullable|string|max:50',
            'photo' => 'nullable|file|image|max:5120',
        ]);

        $data = [
            'shipment_id' => $validated['shipment_id'],
            'shelf' => $validated['shelf'] ?? null,
            'intake_by' => $request->user()->id ?? null,
        ];

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('warehouse_inventory', 'public');
            $data['photo_path'] = $path;
        }

        WarehouseInventory::create($data);

        return redirect()->route('warehouse-inventory.index')->with('success','Item added to warehouse inventory.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = WarehouseInventory::with('shipment','intakeBy')->findOrFail($id);
        return Inertia::render('WarehouseInventory/Show', [
            'item' => $item,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $item = WarehouseInventory::findOrFail($id);
        $shipments = Shipment::orderBy('shipment_code')->get();
        return Inertia::render('WarehouseInventory/Edit', [
            'item' => $item,
            'shipments' => $shipments,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $item = WarehouseInventory::findOrFail($id);
        $validated = $request->validate([
            'shipment_id' => 'required|exists:shipments,id',
            'shelf' => 'nullable|string|max:50',
            'photo' => 'nullable|file|image|max:5120',
        ]);

        $data = [
            'shipment_id' => $validated['shipment_id'],
            'shelf' => $validated['shelf'] ?? null,
        ];

        if ($request->hasFile('photo')) {
            // delete old file if exists
            if ($item->photo_path && Storage::disk('public')->exists($item->photo_path)) {
                Storage::disk('public')->delete($item->photo_path);
            }
            $path = $request->file('photo')->store('warehouse_inventory', 'public');
            $data['photo_path'] = $path;
        }

        $item->update($data);

        return redirect()->route('warehouse-inventory.index')->with('success','Warehouse item updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = WarehouseInventory::findOrFail($id);
        if ($item->photo_path && Storage::disk('public')->exists($item->photo_path)) {
            Storage::disk('public')->delete($item->photo_path);
        }
        $item->delete();

        return redirect()->route('warehouse-inventory.index')->with('success','Warehouse item removed.');
    }
}
