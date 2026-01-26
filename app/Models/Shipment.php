<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;

class Shipment extends Model
{
    use \App\Traits\Auditable;
    protected $fillable = [
        'shipment_code',
        'customer_id',
        'supplier_name',
        'weight',
        'shelf_code',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Fields to exclude from audit logs for shipments.
     *
     * @var array<string>
     */
    protected array $auditExclude = [];
}
