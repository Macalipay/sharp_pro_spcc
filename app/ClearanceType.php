<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClearanceType extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];
}
