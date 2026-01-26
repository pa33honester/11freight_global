<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    /** Display a listing of payments. */
    public function index()
    {
        $payments = Payment::with('customer','approvedBy')->latest()->paginate(12);
        return Inertia::render('Payments/Index', [
            'payments' => $payments,
        ]);
    }

    /** Show create form. */
    public function create()
    {
        $customers = Customer::orderBy('full_name')->get();
        return Inertia::render('Payments/Create', [
            'customers' => $customers,
        ]);
    }

    /** Store new payment. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'reference_code' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'status' => 'nullable|in:PENDING,APPROVED,REJECTED',
        ]);

        $data['status'] = $data['status'] ?? 'PENDING';
        $data['approved_by'] = $request->user()->id ?? null;

        Payment::create($data);

        return redirect()->route('payments.index')->with('success','Payment recorded.');
    }

    /** Show single payment. */
    public function show($id)
    {
        $payment = Payment::with('customer','approvedBy')->findOrFail($id);
        return Inertia::render('Payments/Show', ['payment' => $payment]);
    }

    /** Show edit form. */
    public function edit($id)
    {
        $payment = Payment::findOrFail($id);
        $customers = Customer::orderBy('full_name')->get();
        return Inertia::render('Payments/Edit', ['payment' => $payment, 'customers' => $customers]);
    }

    /** Update payment. */
    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'reference_code' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:PENDING,APPROVED,REJECTED',
        ]);

        if ($data['status'] === 'APPROVED') {
            $data['approved_by'] = $request->user()->id ?? $payment->approved_by;
        }

        $payment->update($data);

        return redirect()->route('payments.index')->with('success','Payment updated.');
    }

    /** Delete payment. */
    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();
        return redirect()->route('payments.index')->with('success','Payment deleted.');
    }
}
