<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollCalendar extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'type',
        'start_date',
        'end_date',
        'payment_date',
        'start_day',
        'end_day',
        'payment_day',
        'set_as_default',
        'status',
        'workstation_id',
        'created_by',
        'updated_by'
    ];
}
