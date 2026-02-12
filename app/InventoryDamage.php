<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryDamage extends Model
{
    protected $fillable = [
        'inventory_id',
        'quantity',
        'date',
        'remarks',
        'created_by',
        'updated_by',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }
}
