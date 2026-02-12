<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeMovement extends Model
{
    protected $fillable = [
        'employee_id',
        'movement_type',
        'prev_records',
        'new_records',
        'effective_date',
        'reason',
        'remarks',
        'reference',
        'status',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_date'
    ];
}
