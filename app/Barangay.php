<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    protected $fillable = [
        'code',
        'name',
        'region_id',
        'province_id',
        'city_id',
    ];
}
