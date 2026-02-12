<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TimeLogApprovals extends Model
{
    protected $fillable = [
        'employee_id',
        'start_date',
        'end_date',
        'status',
        'workstation_id',
        'gross_earnings',
        'sss',
        'pagibig',
        'philhealth',
        'tax',
        'net_pay',
        'created_by',
        'updated_by'
    ];
}
