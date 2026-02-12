<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuditTrails extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'purchase_order_detail_id',
        'item_id',
        'sent_quantity',
        'dr_sequence',
        'dr_id',
        'amount',
        'remark',
        'event_type',
        'project_id',
        'created_at',
        'updated_at',
    ];
    public $timestamps = true;
} 