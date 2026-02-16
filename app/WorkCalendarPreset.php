<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkCalendarPreset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'selected_days',
        'time_in',
        'time_off',
        'is_flexi_time',
        'start_day',
        'start_time',
        'end_day',
        'end_time',
        'workstation_id',
        'created_by',
        'updated_by',
    ];
}
