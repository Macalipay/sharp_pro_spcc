<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OwnerSuppliedMaterialTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'inventory_id',
        'quantity',
        'date',
        'remarks',
        'deleted_at',
        'created_by',
        'updated_by',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }
}
