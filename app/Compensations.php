<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compensations extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'annual_salary',
        'monthly_salary',
        'semi_monthly_salary',
        'weekly_salary',
        'daily_salary',
        'hourly_salary',
        'tax',
        'government_mandated_benefits',
        'other_company_benefits',
        'employee_id',
        'sss',
        'phic',
        'pagibig',
        'created_by',
        'updated_by'
    ];
}
