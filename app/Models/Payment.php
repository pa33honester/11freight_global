<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    public $timestamps = false; // migration only creates created_at

    protected $fillable = [
        'customer_id',
        'reference_code',
        'amount',
        'status',
        'approved_by',
        'created_at',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
