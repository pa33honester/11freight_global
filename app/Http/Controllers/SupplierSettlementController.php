<?php

namespace App\Http\Controllers;

use App\Models\SupplierSettlement;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierSettlementController extends Controller
{
    /** Display a listing of supplier settlements. */
    public function index()
    {
        $settlements = SupplierSettlement::with('payment')->latest()->paginate(12);
        return Inertia::render('SupplierSettlements/Index', [
            'settlements' => $settlements,
        ]);
    }

    /** Show create form. */
    public function create()
    {
        $payments = Payment::orderBy('id')->get();
        return Inertia::render('SupplierSettlements/Create', [
            'payments' => $payments,
        ]);
    }

    /** Store new supplier settlement. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'payment_id' => 'nullable|exists:payments,id',
            'supplier_name' => 'nullable|string|max:150',
            'proof_path' => 'nullable|string',
            'status' => 'nullable|in:PENDING,PAID',
        ]);

        $data['status'] = $data['status'] ?? SupplierSettlement::STATUS_PENDING;
        $data['created_at'] = now();

        SupplierSettlement::create($data);

        return redirect()->route('supplier-settlements.index')->with('success','Supplier settlement recorded.');
    }

    /** Show single settlement. */
    public function show($id)
    {
        $settlement = SupplierSettlement::with('payment')->findOrFail($id);
        // Render the index page and provide the settlement to open in a modal
        $settlements = SupplierSettlement::with('payment')->latest()->paginate(12);
        return Inertia::render('SupplierSettlements/Index', [
            'settlements' => $settlements,
            'viewSettlement' => $settlement,
        ]);
    }

    /** Show edit form. */
    public function edit($id)
    {
        $settlement = SupplierSettlement::findOrFail($id);
        $payments = Payment::orderBy('id')->get();
        return Inertia::render('SupplierSettlements/Edit', ['settlement' => $settlement, 'payments' => $payments]);
    }

    /** Update settlement. */
    public function update(Request $request, $id)
    {
        $settlement = SupplierSettlement::findOrFail($id);

        $data = $request->validate([
            'payment_id' => 'nullable|exists:payments,id',
            'supplier_name' => 'nullable|string|max:150',
            'proof_path' => 'nullable|string',
            'status' => 'required|in:PENDING,PAID',
        ]);

        $settlement->update($data);

        return redirect()->route('supplier-settlements.index')->with('success','Supplier settlement updated.');
    }

    /** Delete settlement. */
    public function destroy($id)
    {
        $settlement = SupplierSettlement::findOrFail($id);
        $settlement->delete();
        return redirect()->route('supplier-settlements.index')->with('success','Supplier settlement deleted.');
    }
}
