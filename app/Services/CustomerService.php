<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Str;

class CustomerService
{
    /**
     * Create a new customer.
     *
     * @param  array  $data
     * @return \App\Models\Customer
     */
    public function create(array $data): Customer
    {
        if (empty($data['customer_code'])) {
            $data['customer_code'] = $this->generateCustomerCode();
        }

        return Customer::create($data);
    }

    /**
     * Update an existing customer.
     *
     * @param  \App\Models\Customer  $customer
     * @param  array  $data
     * @return \App\Models\Customer
     */
    public function update(Customer $customer, array $data): Customer
    {
        $customer->fill($data);
        $customer->save();

        return $customer;
    }

    /**
     * Find a customer by customer code.
     *
     * @param  string  $code
     * @return \App\Models\Customer|null
     */
    public function findByCode(string $code): ?Customer
    {
        return Customer::where('customer_code', $code)->first();
    }

    /**
     * Return all customers ordered by full name.
     *
     * @return \Illuminate\Support\Collection
     */
    public function allOrderedByName()
    {
        return Customer::orderBy('full_name')->get();
    }

    /**
     * Paginate latest customers.
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginateLatest(int $perPage = 10)
    {
        return Customer::latest()->paginate($perPage);
    }

    /**
     * Delete a customer.
     *
     * @param  \App\Models\Customer  $customer
     * @return void
     */
    public function delete(Customer $customer): void
    {
        $customer->delete();
    }

    /**
     * Generate a unique customer code.
     *
     * @return string
     */
    protected function generateCustomerCode(): string
    {
        do {
            $code = Str::upper('CUST-'.Str::random(8));
        } while (Customer::where('customer_code', $code)->exists());

        return $code;
    }
}
