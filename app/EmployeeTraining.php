<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeTraining extends Model
{
    protected $fillable = [
        'training_no',
        'training_name',
        'training_provider',
        'training_description',
        'training_date',
        'training_location',
        'training_duration',
        'training_outcome',
        'training_type',
        'expiration_date',

        'employee_id',
        'created_by',
        'updated_by'
    ];
}
