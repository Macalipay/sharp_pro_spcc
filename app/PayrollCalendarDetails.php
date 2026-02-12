<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollCalendarDetails extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payroll_calendar_header_id',
        'start_date',
        'first_payment',
        'start_of_week',
        'end_of_week'
    ];
}
