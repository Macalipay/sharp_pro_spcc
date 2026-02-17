<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeWorkHistory extends Model
{
    protected $fillable = [
        'company',
        'position',
        'date_hired',
        'date_of_resignation',
        'remarks',
        'attachment',
        'employee_id',
        'created_by',
        'updated_by'
    ];
}
