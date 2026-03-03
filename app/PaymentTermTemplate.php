<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentTermTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'term_text',
        'description',
        'workstation_id',
        'created_by',
        'updated_by',
    ];
}

