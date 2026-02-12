<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employment extends Model
{
    use SoftDeletes;

    //
    protected $fillable = [
        'id',
        'employee_id',
        'classes_id',
        'position_id',
        'department_id',
        'payroll_calendar_id',
        'employment_date',
        'tax_rate',
        'created_by',
        'updated_by'
    ];

    public function employee_information()
    {
        return $this->belongsTo(EmployeeInformation::class, 'employee_id', 'id');
    }

    public function classes()
    {
        return $this->belongsTo(Classes::class, 'classes_id');
    }

    public function positions()
    {
        return $this->belongsTo(Positions::class, 'position_id');
    }

    public function departments()
    {
        return $this->belongsTo(Departments::class, 'department_id');
    }

    public function calendar()
    {
        return $this->belongsTo(PayrollCalendar::class, 'payroll_calendar_id');
    }
}
