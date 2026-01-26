<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_code', 50)->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('supplier_name', 150)->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('shelf_code', 50)->nullable();
            $table->enum('status', [
                'RECEIVED',
                'IN_WAREHOUSE',
                'IN_CONTAINER',
                'DISPATCHED',
                'ARRIVED_GHANA',
                'DELIVERED',
                'VOID',
            ])->default('RECEIVED');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
