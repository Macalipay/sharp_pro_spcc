<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'material_id',
        'po_id',
        'description',
        'total_count',
        'critical_level',
        'quantity_stock',
        'status',
        'deleted_at',
        'project_id',
        'workstation_id',
       
        'created_by',
        'updated_by',
    ];

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
