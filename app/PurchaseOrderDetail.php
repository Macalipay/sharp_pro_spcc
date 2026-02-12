<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_order_id',
        'item',
        'description',
        'quantity',
        'unit_price',
        'discount',
        'tax_rate',
        'total_amount',
        'split',
        'workstation_id',
        'created_by',
        'updated_by',
        'unit_measure'
    ];

    public function purchase_order() {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'id');
    }

    public function item() {
        return $this->belongsTo(Materials::class, 'item', 'id');
    }
    
    public function discount() {
        return $this->hasOne(Discount::class, 'po_id')->where('po_type', 'item');
    }

    public function deliveryReceipts()
    {
        return $this->hasMany(DeliveryReceipt::class);
    }
}
