<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Clearance extends Model
{
    protected $fillable = [
        'employee_id',
        'clearance_date',
        'status'
    ];
}
