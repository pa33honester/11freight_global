<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SupplierSettlement;

class SupplierSettlementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SupplierSettlement::factory()->count(10)->create();
    }
}
