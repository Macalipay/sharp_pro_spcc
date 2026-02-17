<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeEducationalBackground extends Model
{
    protected $fillable = [
        'educational_attainment',
        'course',
        'school_year',
        'school',
        'attachment',
        'employee_id',
        'created_by',
        'updated_by',
    ];
}
