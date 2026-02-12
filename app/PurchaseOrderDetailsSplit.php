<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetailsSplit extends Model
{
    //

    protected $fillable = [
        'purchased_order_details_id',
        'site_id',
        'amount',
        'chart_id'
    ];
}
