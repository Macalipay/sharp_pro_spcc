<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduleRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'request_no',
        'request_date',
        'schedule_type',
        'period_start',
        'period_end',
        'start_time',
        'end_time',
        'reason',
        'status',
        'remarks',
        'requested_by',
        'approved_by',
        'approved_at',
        'workstation_id',
        'created_by',
        'updated_by',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function employee()
    {
        return $this->belongsTo(EmployeeInformation::class, 'employee_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
