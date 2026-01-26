<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shipments = Shipment::with('customer')->latest()->paginate(10);

        return Inertia::render('Shipments/Index', [
            'shipments' => $shipments,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::orderBy('full_name')->get();
        return Inertia::render('Shipments/Create', [
            'customers' => $customers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shipment_code' => 'required|string|max:50|unique:shipments,shipment_code',
            'customer_id' => 'required|exists:customers,id',
            'supplier_name' => 'nullable|string|max:150',
            'weight' => 'nullable|numeric',
            'shelf_code' => 'nullable|string|max:50',
            'status' => 'nullable|in:RECEIVED,IN_WAREHOUSE,IN_CONTAINER,DISPATCHED,ARRIVED_GHANA,DELIVERED,VOID',
        ]);

        Shipment::create($validated);

        return redirect()->route('shipments.index')
            ->with('success', 'Shipment created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $shipment = Shipment::with('customer')->findOrFail($id);
        return Inertia::render('Shipments/Show', [
            'shipment' => $shipment,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $shipment = Shipment::findOrFail($id);
        $customers = Customer::orderBy('full_name')->get();
        return Inertia::render('Shipments/Edit', [
            'shipment' => $shipment,
            'customers' => $customers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $shipment = Shipment::findOrFail($id);

        $validated = $request->validate([
            'shipment_code' => 'required|string|max:50|unique:shipments,shipment_code,' . $shipment->id,
            'customer_id' => 'required|exists:customers,id',
            'supplier_name' => 'nullable|string|max:150',
            'weight' => 'nullable|numeric',
            'shelf_code' => 'nullable|string|max:50',
            'status' => 'nullable|in:RECEIVED,IN_WAREHOUSE,IN_CONTAINER,DISPATCHED,ARRIVED_GHANA,DELIVERED,VOID',
        ]);

        $shipment->update($validated);

        return redirect()->route('shipments.index')
            ->with('success', 'Shipment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $shipment = Shipment::findOrFail($id);
        $shipment->delete();

        return redirect()->route('shipments.index')
            ->with('success', 'Shipment deleted successfully.');
    }
}
