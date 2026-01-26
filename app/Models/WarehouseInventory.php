<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Shipment;
use App\Models\User;

class WarehouseInventory extends Model
{
    protected $table = 'warehouse_inventory';

    protected $fillable = [
        'shipment_id',
        'shelf',
        'photo_path',
        'intake_by',
        'intake_time',
    ];

    protected $appends = ['photo_url'];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function intakeBy()
    {
        return $this->belongsTo(User::class, 'intake_by');
    }

    public function getPhotoUrlAttribute()
    {
        if (! $this->photo_path) {
            return null;
        }

        return Storage::url($this->photo_path);
    }
}
