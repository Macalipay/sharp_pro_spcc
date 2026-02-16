<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollSummaryDetails extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "employee_id",
        "sequence_no",
        "summary_id",
        "gross_earnings",
        "sss",
        "pagibig",
        "philhealth",
        "daily",
        "monthly",
        "hourly",
        "tax",
        "net_pay",
        "status",
        "payslip_status",
        "payslip_sent_at",
        "payslip_sent_by",
        "workstation_id",
        "created_by",
        "updated_by",
    ];

    public function employee() {
        return $this->hasOne(EmployeeInformation::class, 'id', 'employee_id');
    }

    public function header() {
        return $this->hasOne(PayrollSummary::class, 'id', 'summary_id');
    }

    public function timelogs() {
        return $this->hasMany(TimeLogs::class, 'employee_id', 'employee_id');
    }

    public function schedule() {
        return $this->belongsTo(WorkCalendar::class, 'employee_id', 'employee_id');
    }
    
    public function ot_request() {
        return $this->hasMany(OvertimeRequest::class, 'employee_id', 'employee_id');
    }

    public function leave_request() {
        return $this->hasMany(LeaveRequest::class, 'employee_id', 'employee_id');
    }
}
