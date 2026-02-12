<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkCalendar extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id',
        'sunday_start_time',
        'sunday_end_time',
        'monday_start_time',
        'monday_end_time',
        'tuesday_start_time',
        'tuesday_end_time',
        'wednesday_start_time',
        'wednesday_end_time',
        'thursday_start_time',
        'thursday_end_time',
        'friday_start_time',
        'friday_end_time',
        'saturday_start_time',
        'saturday_end_time',
        'employee_id',
        'created_by',
        'updated_by',
    ];
}
