<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierSettlement extends Model
{
    use HasFactory, \App\Traits\Auditable;

    protected $table = 'supplier_settlements';

    public $timestamps = false; // record uses created_at only

    protected $fillable = [
        'payment_id',
        'supplier_name',
        'proof_path',
        'status',
        'created_at',
    ];

    /**
     * Fields to exclude from audit logs.
     *
     * @var array<string>
     */
    protected array $auditExclude = [
        'proof_path',
    ];

    public const STATUS_PENDING = 'PENDING';
    public const STATUS_PAID = 'PAID';

    public static function statuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_PAID];
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
