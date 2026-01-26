<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use \App\Traits\Auditable;

    protected $fillable = [
        'full_name',
        'phone',
        'whatsapp_number',
        'customer_code',
    ];

    /**
     * Exclude PII from audit logs.
     *
     * @var array<string>
     */
    protected array $auditExclude = [
        'phone',
        'whatsapp_number',
    ];
}
