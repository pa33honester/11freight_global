<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\AuditLog;
use App\Services\AuditLogService;
use App\Services\ReceiptService;
use App\Services\CustomerService;
use App\Services\WarehouseService;
use App\Services\ShipmentService;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, WarehouseService $service, CustomerService $customerService)
    {
        $filters = [
            'q' => $request->query('q'),
            'shipment_id' => $request->query('shipment_id'),
            'page' => $request->query('page'),
        ];

        $perPage = (int) $request->query('per_page', 10);

        $items = $service->paginate($filters, $perPage);

        $customers = $customerService->allOrderedByName()->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->full_name,
            ];
        });

        return Inertia::render('Warehouse/Index', [
            'items' => $items,
            'customers' => $customers,
            'filters' => [
                'q' => $request->query('q'),
                'shipment_id' => $request->query('shipment_id'),
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // TODO: implement if needed
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, WarehouseService $service, ReceiptService $receiptService)
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

        $service->create($data);

        // Generate warehouse receipt for the shipment
        try {
            $shipment = \App\Models\Shipment::findOrFail($validated['shipment_id']);
            $receiptService->warehouseReceipt($shipment);
        } catch (\Throwable $e) {
            // non-fatal: continue but record failure in logs if needed
        }

        return redirect()->route('warehouse.index')->with('success','Item added to warehouse inventory.');
    }

    /**
     * Handle warehouse intake: create shipment, inventory record, receipt and audit log.
     */
    public function intake(Request $request, ReceiptService $receiptService, WarehouseService $warehouseService, ShipmentService $shipmentService, AuditLogService $auditLogService)
    {
        // 1. Validate
        $request->validate([
            'customer_id' => 'required',
            'supplier_name' => 'required',
            'weight' => 'required|numeric',
            'photo' => 'required|image'
        ]);

        // 2. Create Shipment
        // Determine shelf_code as ContainerCode-Shelf-Slot
        $containerService = app(\App\Services\ContainerService::class);
        $container = $containerService->firstAvailable();
        $containerCode = $container?->container_code ?? 'C-NA';
        // Simple assignment: shelf (1-20), slot (1-100). Replace with allocation logic later.
        $shelfNumber = rand(1, 20);
        $slotNumber = rand(1, 100);
        $shelfCode = sprintf('%s-S%02d-P%03d', $containerCode, $shelfNumber, $slotNumber);

        $shipment = $shipmentService->create([
            'customer_id' => $request->customer_id,
            'supplier_name' => $request->supplier_name,
            'weight' => $request->weight,
            'shelf_code' => $shelfCode,
            'status' => 'IN_WAREHOUSE'
        ]);

        // 3. Store Photo
        $photoPath = $request->file('photo')->store('shipments','public');

        // 4. Inventory Record
        $inv = $warehouseService->create([
            'shipment_id' => $shipment->id,
            'shelf' => $shipment->shelf_code,
            'photo_path' => $photoPath,
            'intake_by' => Auth::id(),
            'intake_time' => now(),
        ]);

        // 5. Generate Receipt + QR
        try {
            $receiptService->warehouseReceipt($shipment);
        } catch (\Throwable $e) {
            // non-fatal: continue but record failure in logs
        }

        // 6. Audit Log
        $auditLogService->log(Auth::id(), 'WAREHOUSE_INTAKE', 'Warehouse', null, $shipment->toArray(), $request->ip());

        return back()->with('success','Parcel received and logged');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, WarehouseService $service)
    {
        $item = $service->find((int) $id);
        return Inertia::render('Warehouse/Show', [
            'item' => $item,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id, WarehouseService $service, ShipmentService $shipmentService)
    {
        $item = $service->find((int) $id);

        // fetch shipments via service (large per-page to return for select lists)
        $shipmentsPaginator = $shipmentService->paginate([], 1000);
        $shipments = $shipmentsPaginator->items();

        return Inertia::render('Warehouse/Edit', [
            'item' => $item,
            'shipments' => $shipments,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id, WarehouseService $service)
    {
        $item = $service->find((int) $id);
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

        $service->update($item, $data);

        return redirect()->route('warehouse.index')->with('success','Warehouse item updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, WarehouseService $service)
    {
        $service->delete((int) $id);

        return redirect()->route('warehouse.index')->with('success','Warehouse item removed.');
    }
}
