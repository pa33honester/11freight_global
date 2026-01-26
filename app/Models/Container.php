<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Container extends Model
{
    use \App\Traits\Auditable;
    protected $fillable = [
        'container_code',
        'status',
        'departure_date',
        'arrival_date',
    ];

    /**
     * Fields to exclude from audit logs for containers.
     *
     * @var array<string>
     */
    protected array $auditExclude = [];
}
