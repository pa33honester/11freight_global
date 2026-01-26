<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Container extends Model
{
    protected $fillable = [
        'container_code',
        'status',
        'departure_date',
        'arrival_date',
    ];
}
