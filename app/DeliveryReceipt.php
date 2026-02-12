<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeliveryReceipt extends Model
{
    protected $fillable = 
    [
        'purchase_order_detail_id',
        'purchase_order_id',
        'sent_quantity',
        'item_id',
        'project_id',
        'dr_sequence'
        
    ];

    public function purchaseOrderDetail()
    {
        return $this->belongsTo(PurchaseOrderDetail::class);
    }

    public function projectSplit()
    {
        return $this->hasOne(ProjectSplit::class, 'po_id', 'purchase_order_id');
    }
    
    public function getProjectIdAttribute()
    {
        return $this->projectSplit ? $this->projectSplit->project_id : null;
    }
}
