<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CustomerService $customerService)
    {
        $customers = $customerService->paginateLatest(10);

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Customers/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, CustomerService $customerService)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:50',
            'whatsapp_number' => 'nullable|string|max:50',
            'customer_code' => 'nullable|string|max:50|unique:customers,customer_code',
        ]);

        $customerService->create($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return Inertia::render('Customers/Show', [
            'customer' => $customer,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer, CustomerService $customerService)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:50',
            'whatsapp_number' => 'nullable|string|max:50',
            'customer_code' => 'nullable|string|max:50|unique:customers,customer_code,' . $customer->id,
        ]);

        $customerService->update($customer, $validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer, CustomerService $customerService)
    {
        $customerService->delete($customer);

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
