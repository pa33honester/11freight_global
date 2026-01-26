<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;

    protected $table = 'receipts';

    public $timestamps = false; // immutable record with created_at only

    protected $fillable = [
        'receipt_number',
        'type',
        'linked_id',
        'qr_code',
        'created_at',
    ];

    public static function types(): array
    {
        return ['PR','WR','SR','AR','DR','SS'];
    }
}
