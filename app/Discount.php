<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = [
        'name',
        'remarks',
        'po_type',
        'po_id',
        'discount_type',
        'value',
        'workstation_id',
        'created_by',
        'updated_by'
    ];
}
 