<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class LeaveRequest extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'leave_type_id',
        'employee_id',
        'description',
        'start_date',
        'end_date',
        'current_leave_balance',
        'pay_period',
        'total_leave_hours',
        'status',
        'workstation_id',
        'created_by',
        'updated_by',
    ];

    public function leave_type() {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    public function employee() {
        return $this->belongsTo(EmployeeInformation::class, 'employee_id');
    }

    public static function getDaysPerMonth($leaveRequestId, $year)
    {
        $leaveRequest = self::where('employee_id', $leaveRequestId)->whereYear('pay_period', $year)->first();

        if (!$leaveRequest) {
            return [];
        }

        $start = Carbon::parse($leaveRequest->start_date);
        $end = Carbon::parse($leaveRequest->end_date);
        $end->modify('+1 day');

        $daysPerMonth = [];

        foreach (new \DatePeriod($start, new \DateInterval('P1D'), $end) as $date) {
            $month = $date->format('m');

            if (!isset($daysPerMonth[$month])) {
                $daysPerMonth[$month] = 0;
            }

            $daysPerMonth[$month]++;
        }

        return $daysPerMonth;
    }
    
}