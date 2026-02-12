<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Allowance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'amount',
        'workstation_id',
        'created_by',
        'updated_by',
    ];
}
