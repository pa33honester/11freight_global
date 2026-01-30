<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\ShipmentStatus;
use App\Models\Customer;
use Illuminate\Support\Str;

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

    /**
     * Cast attributes.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'status' => ShipmentStatus::class,
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

    /**
     * Set a unique shipment_code automatically when creating.
     */
    protected static function booted()
    {
        static::creating(function ($shipment) {
            if (empty($shipment->shipment_code)) {
                $shipment->shipment_code = '11F-' . (string) Str::uuid();
            }
        });
    }
    /**
     * Generate a unique shipment code using UUIDs.
     */
    public static function generateUniqueCode(string $prefix = '11F-'): string
    {
        return $prefix . (string) Str::uuid();
    }

    /**
     * Return available status values.
     *
     * @return string[]
     */
    public static function statuses(): array
    {
        return ShipmentStatus::values();
    }
}
