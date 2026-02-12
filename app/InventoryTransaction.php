<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'inventory_id',
        'material_id',
        'project_id',
        'po_id',
        'quantity',
        'date',
         'code',
        'requested_by',
        'issued_by',
        'approved_by',
        'remarks',
        'deleted_at',
        'created_by',
        'updated_by',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    public function material()
    {
        return $this->belongsTo(Materials::class, 'material_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function purchase_order()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }
}
