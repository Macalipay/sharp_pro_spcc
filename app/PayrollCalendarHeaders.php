<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollCalendarHeaders extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'workstation_id',
        'name',
        'calendar_type',
        'frequency',
        'set_as_default',
        'status',
        'created_by',
        'updated_by'
    ];
    
    public function details() {
        return $this->hasOne(PayrollCalendarDetails::class, 'payroll_calendar_header_id', 'id');
    }
}
