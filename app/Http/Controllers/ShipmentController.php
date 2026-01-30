<?php

namespace App\Http\Controllers;

use App\Services\CustomerService;
use App\Services\ShipmentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ShipmentService $service)
    {
        $filters = $request->only(['q', 'customer_id', 'status', 'page']);
        $perPage = (int) $request->input('per_page', 10);
        $shipments = $service->paginate($filters, $perPage);

        return Inertia::render('Shipments/Index', [
            'shipments' => $shipments,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(CustomerService $customerService)
    {
        $customers = $customerService->allOrderedByName();
        return Inertia::render('Shipments/Create', [
            'customers' => $customers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ShipmentService $service)
    {
        $validated = $request->validate([
            // shipment_code is optional on create; the model will auto-generate if absent
            'shipment_code' => 'nullable|string|max:50|unique:shipments,shipment_code',
            'customer_id' => 'required|exists:customers,id',
            'supplier_name' => 'nullable|string|max:150',
            'weight' => 'nullable|numeric',
            'shelf_code' => 'nullable|string|max:50',
            'status' => 'nullable|in:RECEIVED,IN_WAREHOUSE,IN_CONTAINER,DISPATCHED,ARRIVED_GHANA,DELIVERED,VOID',
        ]);

        $service->create($validated);

        return redirect()->route('shipments.index')
            ->with('success', 'Shipment created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, ShipmentService $service)
    {
        $shipment = $service->findOrFail((int) $id, ['customer']);

        return Inertia::render('Shipments/Show', [
            'shipment' => $shipment,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id, CustomerService $customerService, ShipmentService $service)
    {
        $shipment = $service->findOrFail((int) $id);
        $customers = $customerService->allOrderedByName();

        return Inertia::render('Shipments/Edit', [
            'shipment' => $shipment,
            'customers' => $customers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id, ShipmentService $service)
    {
        $shipment = $service->findOrFail((int) $id);

        $validated = $request->validate([
            'shipment_code' => 'required|string|max:50|unique:shipments,shipment_code,' . $shipment->id,
            'customer_id' => 'required|exists:customers,id',
            'supplier_name' => 'nullable|string|max:150',
            'weight' => 'nullable|numeric',
            'shelf_code' => 'nullable|string|max:50',
            'status' => 'nullable|in:RECEIVED,IN_WAREHOUSE,IN_CONTAINER,DISPATCHED,ARRIVED_GHANA,DELIVERED,VOID',
        ]);

        $service->update($shipment, $validated);

        return redirect()->route('shipments.index')
            ->with('success', 'Shipment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, ShipmentService $service)
    {
        $service->delete((int) $id);

        return redirect()->route('shipments.index')
            ->with('success', 'Shipment deleted successfully.');
    }
}
