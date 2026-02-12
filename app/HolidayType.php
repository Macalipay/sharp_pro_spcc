<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HolidayType extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'multiplier',
        'workstation_id',
        'created_by',
        'updated_by'
    ];
}
