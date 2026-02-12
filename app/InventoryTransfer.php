<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryTransfer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'inventory_id',
        'from_project',
        'to_project',
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

    public function project()
    {
        return $this->belongsTo(Project::class, 'to_project');
    }
}
