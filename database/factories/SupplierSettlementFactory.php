<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\SupplierSettlement;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupplierSettlement>
 */
class SupplierSettlementFactory extends Factory
{
    protected $model = SupplierSettlement::class;

    public function definition(): array
    {
        $statuses = SupplierSettlement::statuses();

        return [
            'payment_id' => null,
            'supplier_name' => $this->faker->company(),
            'proof_path' => null,
            'status' => $this->faker->randomElement($statuses),
            'created_at' => now(),
        ];
    }
}
