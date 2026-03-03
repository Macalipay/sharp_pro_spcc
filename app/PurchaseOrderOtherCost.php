<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderOtherCost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_order_id',
        'description',
        'amount',
        'created_by',
        'updated_by',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'id');
    }
}
