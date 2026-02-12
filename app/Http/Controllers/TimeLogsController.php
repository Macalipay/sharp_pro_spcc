<?php

namespace App\Http\Controllers;

use Auth;
use DateTime;
use DatePeriod;
use DateInterval;
use App\Absent;
use App\TimeLogs;
use App\TimeLogApprovals;
use App\Schedulings;
use App\Earnings;
use App\LeaveRequest;
use App\OvertimeRequest;
use App\Deductions;
use App\DeductionSetup;
use App\Departments;
use App\WorkCalendar;
use App\Holiday;
use App\EarningSetup;
use App\Allowance;
use App\AllowanceSetup;
use App\PayrollCalendar;
use App\EmployeeAdjustment;
use App\EmployeeInformation;
use App\Classes\TimeKeeping\TimeLog;
use App\Classes\Computation\TimeLog as TimeComputation;
use App\Classes\Computation\Payroll\SSS as SSS_Benefits;
use App\Classes\Computation\Payroll\Salary as SalaryComputation;
use App\Classes\Computation\Payroll\WithholdingTax as WithholdingTax_Benefits;
use Illuminate\Http\Request;
use stdClass;


use App\Http\Controllers\ActivityController;

class TimeLogsController extends Controller
{   
    protected $sss_val, $withholding_tax;

    public function __construct() 
    {
        $this->sss_val = new SSS_Benefits();
        $this->withholding_tax = new WithholdingTax_Benefits();
    }

    public function index()
    {
        $departments = Departments::get();
        $allowances = Allowance::get();
        $deductions = Deductions::where('status', 1)->get();
        $canDownload = auth()->user()->can('print_Time Logs');

        return view('backend.pages.transaction.timekeeping.time_logs', ["type"=>"full-view"], compact('departments', 'allowances', 'deductions','canDownload'));
    }
    public function get($department, $first, $last) {
        
        $timelog = new TimeLog;
        $script = $timelog->query($first, $last);

        if(request()->ajax()) {
            if($department === "all") {
                $record = EmployeeInformation::selectRaw($script)
                ->leftJoin('time_logs', 'employees.id', '=', 'time_logs.employee_id')
                ->leftJoin('earnings', 'earnings.id', '=', 'time_logs.type')
                ->whereHas('employments_tab', function($query) {
                    $query;
                })->with(['approval' => function($query) use($first, $last) {
                    $query->where(function($query) use ($first, $last){
                        $query->where('start_date', '>=', $first)->where('start_date', '<=', $last);
                    })->orWhere(function($query) use ($first, $last){
                        $query->where('end_date', '>=', $first)->where('end_date', '<=', $last);
                    });
                }])
                ->groupBy("employees.id")
                ->get();
            }
            else {
                $record = EmployeeInformation::selectRaw($script)
                ->leftJoin('time_logs', 'employees.id', '=', 'time_logs.employee_id')
                ->leftJoin('earnings', 'earnings.id', '=', 'time_logs.type')
                ->leftJoin('employments', 'employees.id', '=', 'employments.employee_id')
                ->where('employments.department_id', '=', $department)
                ->whereHas('employments_tab', function($query) {
                    $query;
                })->with(['approval' => function($query) use($first, $last) {
                    $query->where(function($query) use ($first, $last){
                        $query->where('start_date', '<=', $first)->where('end_date', '>=', $first);
                    })->where(function($query) use ($first, $last){
                        $query->where('start_date', '<=', $last)->where('end_date', '>=', $last);
                    });
                }])
                ->groupBy("employees.id")
                ->get();
            }

            return datatables()->of(
                $record
            )
            ->addIndexColumn()
            ->make(true);
        }
    }
    
    public function save(Request $request)
    {  
        $activity = new ActivityController();

        foreach($request->record as $record) {
            if($record['time_in'] !== null || $record['time_out'] !== null) {
                $time_logs = TimeLogs::where('employee_id', $record['employee_id'])->where('date', $record['date'])->count();
                if($time_logs === 0) {
                    $record['workstation_id'] = Auth::user()->workstation_id;
                    $record['created_by'] = Auth::user()->id;
                    $record['updated_by'] = Auth::user()->id;
            
                    $timelogs = TimeLogs::create($record);

                    $activity->save(array(
                        "subject" => "New Timelogs",
                        "details" => "Timelogs Created",
                        "module" => "timelogs",
                        "source_id" => $timelogs->id,
                        "link" => "/payroll/time_logs",
                        "type" => "transaction",
                        "role" => Auth::user()->roles->first()->name,
                        "status" => 0,
                        "workstation_id" => Auth::user()->workstation_id,
                        "created_by" => Auth::user()->id,
                        "updated_by" => Auth::user()->id,
                    ));
                }
                else {
                    $timelogs = TimeLogs::where('employee_id', $record['employee_id'])->where('date', $record['date'])->first();
                    TimeLogs::where('employee_id', $record['employee_id'])->where('date', $record['date'])->update($record);
                    
                    $activity->save(array(
                        "subject" => "Update Timelogs",
                        "details" => "Timelogs Updated",
                        "module" => "timelogs",
                        "source_id" => $timelogs->id,
                        "link" => "/payroll/time_logs",
                        "type" => "transaction",
                        "role" => Auth::user()->roles->first()->name,
                        "status" => 0,
                        "workstation_id" => Auth::user()->workstation_id,
                        "created_by" => Auth::user()->id,
                        "updated_by" => Auth::user()->id,
                    ));
                }
            } 
        }

        return response()->json();
    }
    
    public function plot($employee_id, $first, $last) {

        $timelog = new TimeLog;
        $script = $timelog->time_logs($first, $last);

        $record = TimeLogs::selectRaw($script)
        ->where("time_logs.employee_id", "=", $employee_id)
        ->get();

        return datatables()->of(
            $record
        )
        ->addIndexColumn()
        ->make(true);
    }

    public function get_earnings(Request $request) {
        $earnings = Earnings::get();
        
        return response()->json(compact('earnings'));
    }

    public function update_status(Request $request) {
        TimeLogs::where('id', $request->id)->update(['status'=>$request->status]);
    }

    public function cross_matching(Request $request) {

        $computation = new TimeComputation;
        
        $date = $request->data['date'];
        $employee_id = $request->data['employee_id'];
        $time_in = $request->data['time_in'];
        $time_out = $request->data['time_out'];
        $type = $request->data['type'];

        $in = strtotime($time_in);
        $out = strtotime($time_out);

        $data = null;
        $schedule = Schedulings::where('date', $date)->where('employee_id', $employee_id)->where('type', 0);

        if($schedule->count() !== 0) {
            $schedule = $schedule->firstOrFail();
            
            $start = strtotime($schedule->start_time);
            $end = strtotime($schedule->end_time);
            
            $late_hours = $computation->late_hours($in, $start);
            $overtime = $computation->overtime($out, $end);
            $undertime = $computation->undertime($out, $end);
            $sub_total = $computation->subtotal($start, $end, $out);
            $total_hours = $computation->total_hours($sub_total, $late_hours, $overtime, $undertime);
            
            $time_logs = TimeLogs::where('date', $date)->where('employee_id', $employee_id);
            
            if($time_logs->count() === 0 ) {
                $data = array(
                    "employee_id" => $employee_id,
                    "date" => $date,
                    "time_in" => $time_in,
                    "time_out" => $time_out,
                    "total_hours" => $total_hours,
                    "break_hours" => 0,
                    "ot_hours" => $overtime,
                    "late_hours" => $late_hours,
                    "undertime" => $undertime,
                    "type" => $type,
                    "status" => 0,
                    "schedule_status" => 1,
                    "workstation_id" => Auth::user()->workstation_id,
                    "created_by" => Auth::user()->id,
                    "updated_by" => Auth::user()->id
                );
                
                TimeLogs::create($data);
            }
            else {
                $time_logs = $time_logs->firstOrFail();

                $data = array(
                    "time_in" => $time_in,
                    "time_out" => $time_out,
                    "total_hours" => $total_hours,
                    "ot_hours" => $overtime,
                    "late_hours" => $late_hours,
                    "undertime" => $undertime,
                    "type" => $type,
                    "schedule_status" => 1
                );

                TimeLogs::where('date', $request->data['date'])->where('employee_id', $request->data['employee_id'])->update($data);
            }
        }
        else {
            $time_logs = TimeLogs::where('date', $date)->where('employee_id', $employee_id);
            if($time_logs->count() === 0 ) {
                $data = array(
                    "employee_id" => $employee_id,
                    "date" => $date,
                    "time_in" => $time_in,
                    "time_out" => $time_out,
                    "total_hours" => $request->data['total_hours'],
                    "break_hours" => 0,
                    "ot_hours" => $request->data['ot_hours'],
                    "late_hours" => $request->data['late_hours'],
                    "undertime" => $request->data['undertime'],
                    "type" => $type,
                    "status" => 0,
                    "schedule_status" => 2,
                    "workstation_id" => Auth::user()->workstation_id,
                    "created_by" => Auth::user()->id,
                    "updated_by" => Auth::user()->id
                );

                TimeLogs::create($data);
            }
            else {
                $data = array(
                    "time_in" => $time_in,
                    "time_out" => $time_out,
                    "total_hours" => $request->data['total_hours'],
                    "ot_hours" => $request->data['ot_hours'],
                    "late_hours" => $request->data['late_hours'],
                    "undertime" => $request->data['undertime'],
                    "type" => $type
                );
                TimeLogs::where('date', $date)->where('employee_id', $employee_id)->update($data);
            }
        }

        return $data;
    }

    public function get_record(Request $request, $id) {

        $ot_earning = Earnings::where('code', 'OT')->first();

        try {
            $record = EmployeeInformation::with(['compensations','employments_tab', 'employments_tab.positions', 'employments_tab.departments', 'employments_tab.calendar', 'approval' => function($query) use($request) {
                $query->where('start_date', '<=', $request->date)->where('end_date', '>=', $request->date);
            }])->where('id', $id)->first();

            $record = $this->applySalaryAdjustment($record, $request->date);

            if($record->employments_tab !== null) {
                $semi_monthly = $this->getSemiMonthly($record->employments_tab->calendar->start_date, $record->employments_tab->calendar->end_date, $request->date, $record->employments_tab->calendar->payment_date, $id)["output"];
                $other = $this->getSemiMonthly($record->employments_tab->calendar->start_date, $record->employments_tab->calendar->end_date, $request->date, $record->employments_tab->calendar->payment_date, $id)["other"];
            }
            else {
                $semi_monthly = null;
                $other = null;
            }

            return response()->json(compact('record', 'semi_monthly', 'other', 'ot_earning'));
        }
        catch(ErrorException $e) {
            return response()->json(['error' => 'Record not found'], 404);
        }
    }

    public function getSemiMonthly($startDate, $endDate, $date, $payment_date, $id) {
        
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $selected = new DateTime($date);
        $payment_date = new DateTime($payment_date);
        
        
        $start_2 = new DateTime($selected->format('Y').'-'.$selected->format('m').'-'.$start->format('d'));
        $end_2 = new DateTime($selected->format('Y').'-'.$selected->format('m').'-'.$end->format('d'));
        
        $paydate = new DateTime($selected->format('Y').'-'.$selected->format('m').'-'.$payment_date->format('d'));
        
        $deductions = Deductions::where('taxable', 0)->get();

        $output = [];

        $new_dates = [
            'first_half' => [
                "start" => $selected->format('Y').'-'.$selected->format('m').'-'.$start->format('d'),
                "end" => $selected->format('Y').'-'.$selected->format('m').'-'.$end->format('d')
            ],
            'second_half' => [
                "start" => $end_2->modify('+1 day')->format('Y-m-d'),
                "end" => $start_2->modify('+1 month')->modify('-1 day')->format('Y-m-d')
            ],
            'third_half' => [
                "start" => $end_2->modify('-1 month')->format('Y-m-d'),
                "end" => $start_2->modify('-1 month')->format('Y-m-d')
            ],
            'date' => $selected->format('Y-m-d')
        ];

        if($selected >= new DateTime($new_dates['first_half']['start']) && $selected <= new DateTime($new_dates['first_half']['end'])) {
            
            $leave_list = LeaveRequest::with('leave_type')->where('employee_id', $id)->whereBetween('start_date', [$new_dates["first_half"]["start"], $new_dates["first_half"]["end"]])->get();

            $holiday = Holiday::with('holiday_type')->where(function($query) use($new_dates) {
                $query->whereMonth('date', (new DateTime($new_dates["first_half"]["start"]))->format('m'))->whereDay('date', '>=', (new DateTime($new_dates["first_half"]["start"]))->format('d'));
            })->orWhere(function($query) use($new_dates) {
                $query->whereMonth('date', (new DateTime($new_dates["first_half"]["end"]))->format('m'))->whereDay('date', '>=', (new DateTime($new_dates["first_half"]["end"]))->format('d'));
            })->get();
            
            $ot_data = OvertimeRequest::where(function($query) use($new_dates) {
                $startDate = (new DateTime($new_dates["first_half"]["start"]))->format('Y-m-d');
                $endDate = (new DateTime($new_dates["first_half"]["end"]))->format('Y-m-d');
                
                $query->whereBetween('ot_date', [$startDate, $endDate]);
            })->where('status', 'approved')->where('employee_id', $id)->sum('total_hours');


            $output = $this->generateOutput('first_half', $new_dates, $id);
            $other = [
                "pay_date" => $paydate->format('Y-m-d'),
                "pay_period" => $new_dates["first_half"]["end"],
                "earnings" => EarningSetup::with(['earning' => function($query) {
                    $query->where("code", "RE")->orWhere("code", "OT");
                }])->where('employee_id', $id)->get(),
                "allowances" => AllowanceSetup::selectRaw('SUM(allowance_setups.amount) as total, SUM(allowance_setups.total_amount) as grand_total, SUM(allowance_setups.days) as days, allowances.name as allowance')->join('allowances', 'allowances.id', 'allowance_setups.allowance_id')->where('sequence_no', 'M-'.(new DateTime($new_dates["first_half"]["end"]))->format('mdY'))->where('employee_id', $id)->groupBy('allowance_id')->get(),
                "deductions" => DeductionSetup::selectRaw('SUM(amount) as total, deductions.name as deduction')->join('deductions', 'deductions.id', 'deduction_setups.deduction_id')->where('sequence_no', 'M-'.(new DateTime($new_dates["first_half"]["end"]))->format('mdY'))->where('employee_id', $id)->groupBy('deduction_id')->get(),
                "leave" => $leave_list,
                "holiday" => $holiday,
                "overtime" => $ot_data,
                "dates" => $new_dates
                // "deductions" => $deductions
            ];
        }
        else if($selected >= new DateTime($new_dates['second_half']['start']) && $selected <= new DateTime($new_dates['second_half']['end'])){
            $leave_list = LeaveRequest::with('leave_type')->where('employee_id', $id)->whereBetween('start_date', [$new_dates["second_half"]["start"], $new_dates["second_half"]["end"]])->get();
            
            $holiday = Holiday::with('holiday_type')->where(function($query) use($new_dates) {
                $query->whereMonth('date', (new DateTime($new_dates["second_half"]["start"]))->format('m'))->whereDay('date', '>=', (new DateTime($new_dates["second_half"]["start"]))->format('d'));
            })->orWhere(function($query) use($new_dates) {
                $query->whereMonth('date', (new DateTime($new_dates["second_half"]["end"]))->format('m'))->whereDay('date', '>=', (new DateTime($new_dates["second_half"]["end"]))->format('d'));
            })->get();
            
            $ot_data = OvertimeRequest::where(function($query) use($new_dates) {
                $startDate = (new DateTime($new_dates["second_half"]["start"]))->format('Y-m-d');
                $endDate = (new DateTime($new_dates["second_half"]["end"]))->format('Y-m-d');

                $query->whereBetween('ot_date', [$startDate, $endDate]);
            })->where('status', 'approved')->groupBy('employee_id')->sum('total_hours');
            
            $output = $this->generateOutput('second_half', $new_dates, $id);
            $other = [
                "pay_date" => $paydate->format('Y-m-d'),
                "pay_period" => $new_dates["second_half"]["end"],
                "earnings" => EarningSetup::with(['earning' => function($query) {
                    $query->where("code", "RE")->orWhere("code", "OT");
                }])->where('employee_id', $id)->get(),
                "allowances" => AllowanceSetup::selectRaw('SUM(allowance_setups.amount) as total, SUM(allowance_setups.total_amount) as grand_total, SUM(allowance_setups.days) as days, allowances.name as allowance')->join('allowances', 'allowances.id', 'allowance_setups.allowance_id')->where('sequence_no', 'M-'.(new DateTime($new_dates["second_half"]["end"]))->format('mdY'))->where('employee_id', $id)->groupBy('allowance_id')->get(),
                "deductions" => DeductionSetup::selectRaw('SUM(amount) as total, deductions.name as deduction')->join('deductions', 'deductions.id', 'deduction_setups.deduction_id')->where('sequence_no', 'M-'.(new DateTime($new_dates["second_half"]["end"]))->format('mdY'))->where('employee_id', $id)->groupBy('deduction_id')->get(),
                "leave" => $leave_list,
                "holiday" => $holiday,
                "overtime" => $ot_data,
                "dates" => $new_dates
                // "deductions" => $deductions
            ];
        }
        else {
            $leave_list = LeaveRequest::with('leave_type')->where('employee_id', $id)->whereBetween('start_date', [$new_dates["third_half"]["start"], $new_dates["third_half"]["end"]])->get();
            
            $holiday = Holiday::with('holiday_type')->where(function($query) use($new_dates) {
                $query->whereMonth('date', (new DateTime($new_dates["third_half"]["start"]))->format('m'))->whereDay('date', '>=', (new DateTime($new_dates["third_half"]["start"]))->format('d'));
            })->orWhere(function($query) use($new_dates) {
                $query->whereMonth('date', (new DateTime($new_dates["third_half"]["end"]))->format('m'))->whereDay('date', '>=', (new DateTime($new_dates["third_half"]["end"]))->format('d'));
            })->get();
            
            $ot_data = OvertimeRequest::where(function($query) use($new_dates) {
                $startDate = (new DateTime($new_dates["third_half"]["start"]))->format('Y-m-d');
                $endDate = (new DateTime($new_dates["third_half"]["end"]))->format('Y-m-d');

                $query->whereBetween('ot_date', [$startDate, $endDate]);
            })->where('status', 'approved')->groupBy('employee_id')->sum('total_hours');
            
            $output = $this->generateOutput('third_half', $new_dates, $id);
            $other = [
                "pay_date" => $paydate->format('Y-m-d'),
                "pay_period" => $new_dates["third_half"]["end"],
                "earnings" => EarningSetup::with(['earning' => function($query) {
                    $query->where("code", "RE")->orWhere("code", "OT");
                }])->where('employee_id', $id)->get(),
                "allowances" => AllowanceSetup::selectRaw('SUM(allowance_setups.amount) as total, SUM(allowance_setups.total_amount) as grand_total, SUM(allowance_setups.days) as days, allowances.name as allowance')->join('allowances', 'allowances.id', 'allowance_setups.allowance_id')->where('sequence_no', 'M-'.(new DateTime($new_dates["third_half"]["end"]))->format('mdY'))->where('employee_id', $id)->groupBy('allowance_id')->get(),
                "deductions" => DeductionSetup::selectRaw('SUM(amount) as total, deductions.name as deduction')->join('deductions', 'deductions.id', 'deduction_setups.deduction_id')->where('sequence_no', 'M-'.(new DateTime($new_dates["third_half"]["end"]))->format('mdY'))->where('employee_id', $id)->groupBy('deduction_id')->get(),
                "leave" => $leave_list,
                "holiday" => $holiday,
                "overtime" => $ot_data,
                "dates" => $new_dates
                // "deductions" => $deductions
            ];
        }

        return ["output"=>$output, "other"=>$other];
    }

    public function generateOutput($half, $dates, $id) {
        $output = [];
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod(new DateTime($dates[$half]['start']), $interval, (new DateTime($dates[$half]['end']))->modify('+1 day'));

        foreach($period as $dt) {
            try {
                $leave = LeaveRequest::with('leave_type')->where('employee_id', $id)->where('start_date', '<=', $dt->format('Y-m-d'))->where('end_date', '>=', $dt->format('Y-m-d'))->first();
                
                $overtime = OvertimeRequest::where('employee_id', $id)->where('ot_date', '=', $dt->format('Y-m-d'))->where('status', 'approved')->first();
                
                $employee = Timelogs::where('employee_id', $id)->where('date', $dt->format('Y-m-d'))->first();
                $calendar = WorkCalendar::where('employee_id', $id)->first();
                $holiday = Holiday::whereMonth('date', $dt->format('m'))->whereDay('date', $dt->format('d'))->first();

                $status = $calendar !== null?(
                    $calendar[strtolower($dt->format('l'))."_start_time"] !== null?(
                        $holiday !== null?"HOLIDAY":(
                            $leave !== null?$leave->leave_type->leave_name:(
                                $employee !== null?"WORK":"ABSENT"
                            )
                        )
                    ):"OFF"
                ):"OFF";
                
                array_push($output, [
                    "date" => $dt->format('Y-m-d'),
                    "day" => $dt->format('l'),
                    "status" => $status,
                    "time_in" => $employee !== null ? $employee->time_in:null,
                    "break_in" => $employee !== null ? $employee->break_in:null,
                    "break_out" => $employee !== null ? $employee->break_out:null,
                    "time_out" => $employee !== null ? $employee->time_out:null,
                    "ot_in" => $employee !== null ? $employee->ot_in:null,
                    "ot_out" => $employee !== null ? $employee->ot_out:null,
                    "office_hours" => $employee !== null ? $employee->time_in !== null && $employee->time_out !== null ? $this->countHours($employee->time_in, $employee->time_out):"0.00":"0.00",
                    "break_time" => $employee !== null ?  $employee->time_in !== null && $employee->time_out !== null ? "1.00":"0.00":"0.00",
                    // "break_time" => $this->countHours($employee !== null ? $employee->break_in:"0:00", $employee !== null ? $employee->break_out:"0:00"),
                    "overtime" => $overtime !== null ? number_format((float) $overtime->total_hours, 2, '.', '') : "0.00",
                ]);
            } catch(Exception $e) {
                array_push($output, [
                    "date" => "$dt->format('Y-m-d')",
                    "day" => $dt->format('l'),
                    "status" => "-",
                    "time_in" => null,
                    "break_in" => null,
                    "break_out" => null,
                    "time_out" => null,
                    "ot_in" => null,
                    "ot_out" => null,
                    "office_hours" => "0:00",
                    "break_time" => "0:00",
                    "overtime" => "0:00",
                ]);
            }
        }

        return $output;
    }

    public function countHours($time1, $time2) {
        $from = new DateTime($time1);
        $to = new DateTime($time2);
        $interval = $from->diff($to);
        $hours = ($interval->days * 24) + $interval->h + ($interval->i / 60);

        return number_format($hours, 2, '.', '');
    }

    private function applySalaryAdjustment($record, $asOfDate)
    {
        if ($record === null || $record->employments_tab === null) {
            return $record;
        }

        $adjustment = EmployeeAdjustment::where('employee_id', $record->employments_tab->id)
            ->whereIn('adjustment_type', ['SALARY', 'PAYROLL'])
            ->where('status', 'APPROVED')
            ->where('effective_date', '<=', $asOfDate)
            ->orderBy('effective_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($adjustment === null || $adjustment->new_value === null) {
            return $record;
        }

        $salaryComputation = new SalaryComputation();
        $monthly = (float) $adjustment->new_value;
        $daily = (float) $salaryComputation->daily($monthly, 'monthly');
        $hourly = (float) $salaryComputation->hourly($monthly, 'monthly');
        $semiMonthly = (float) $salaryComputation->semi_monthly($monthly, 'monthly');
        $annual = (float) $salaryComputation->annual($monthly, 'monthly');
        $weekly = (float) $salaryComputation->weekly($monthly, 'monthly');

        if ($record->compensations === null) {
            $record->compensations = new stdClass();
        }

        $record->compensations->monthly_salary = $monthly;
        $record->compensations->daily_salary = $daily;
        $record->compensations->hourly_salary = $hourly;
        $record->compensations->semi_monthly_salary = $semiMonthly;
        $record->compensations->annual_salary = $annual;
        $record->compensations->weekly_salary = $weekly;

        return $record;
    }

    public function getSSS(Request $request) {
        $sss = $this->sss_val->getValue($request->gross)->ee;
        $withhold = $this->withholding_tax->getValue($request->gross, $request->type);
        $w_tax = 0;

        if(intval($withhold->range_from) !== 0) {
            $w_tax = floatval((($request->gross - $withhold->range_from)*($withhold->rate_on_excess*0.01))+$withhold->fix_tax);
        }

        return response()->json(compact('sss', 'w_tax'));
    }

    public function get_date(Request $request) {
        $data = array();

        $record = EmployeeInformation::with('compensations','employments_tab', 'employments_tab.calendar')->where('id', $request->employee_id)->first();

        $start = new DateTime($record->employments_tab->calendar->start_date);
        $end = new DateTime($record->employments_tab->calendar->end_date);
        $selected = new DateTime($request->date);
        $payment_date = new DateTime($record->employments_tab->calendar->payment_date);
        
        $start_2 = new DateTime($selected->format('Y').'-'.$selected->format('m').'-'.$start->format('d'));
        $end_2 = new DateTime($selected->format('Y').'-'.$selected->format('m').'-'.$end->format('d'));
        
        $paydate = new DateTime($selected->format('Y').'-'.$selected->format('m').'-'.$payment_date->format('d'));

        $new_start = $end_2->modify('+1 day');
        $new_end = $start_2->modify('+1 month')->modify('-1 day');

        if($selected >= $start && $selected <= $end) {
            $data = array(
                'employee_id' => $request->employee_id,
                'start_date' => $start_2->format('Y-m-d'),
                'end_date' => $end_2->format('Y-m-d')
            );

        }
        else{
            $data = array(
                'employee_id' => $request->employee_id,
                'start_date' => $new_start->format('Y-m-d'),
                'end_date' => $new_end->format('Y-m-d')
            );
        }

        return response()->json(compact('data'));
    }

    public function timelogs_approve(Request $request) {
        $request['status'] = 1;
        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        if($request->absents !== null) {
            foreach($request->absents as $item) {
                Absent::create($item);
            }
        }

        TimeLogApprovals::create($request->all());
    }

    public function get_summary(Request $request) {
        $return = $this->get_date_arrange($request);

        return response()->json(compact('return'));
    }

    public function get_date_arrange($request) {
        $arrange = [];
        $timesheet = 0;

        $record = PayrollCalendar::where('id', $request->calendar)->first();

        $start = new DateTime($record->start_date);
        $end = new DateTime($record->end_date);
        $selected = new DateTime($request->selected);
        $payment_date = new DateTime($record->payment_date);
        
        $start_2 = new DateTime($selected->format('Y').'-'.$selected->format('m').'-'.$start->format('d'));
        $end_2 = new DateTime($selected->format('Y').'-'.$selected->format('m').'-'.$end->format('d'));
        $paydate = new DateTime($selected->format('Y').'-'.$selected->format('m').'-'.$payment_date->format('d'));

        $new_start = (new DateTime($selected->format('Y').'-'.$selected->format('m').'-'.$end->format('d')))->modify('+1 day');
        $new_end = (new DateTime($selected->format('Y').'-'.$selected->format('m').'-'.$start->format('d')))->modify('+1 month')->modify('-1 day');
        $new_pay_date = (new DateTime($selected->format('Y').'-'.$selected->format('m').'-'.$start_2->format('d')))->modify('+'.$end_2->diff($paydate)->format("%r%a").' day')->modify('+1 month');

        
        // $new_dates = [
        //     'first_half' => [
        //         "start" => $selected->format('Y').'-'.$selected->format('m').'-'.$start->format('d'),
        //         "end" => $selected->format('Y').'-'.$selected->format('m').'-'.$end->format('d')
        //     ],
        //     'second_half' => [
        //         "start" => $end_2->modify('+1 day')->format('Y-m-d'),
        //         "end" => $start_2->modify('+1 month')->modify('-1 day')->format('Y-m-d')
        //     ],
        //     'third_half' => [
        //         "start" => $end_2->modify('-1 month')->format('Y-m-d'),
        //         "end" => $start_2->modify('-1 month')->format('Y-m-d')
        //     ],
        //     'date' => $selected->format('Y-m-d')
        // ];

        $prev_start = (new DateTime($selected->format('Y').'-'.$selected->format('m').'-'.$end->format('d')))->modify('+1 day')->modify('-1 month');
        $prev_end = (new DateTime($selected->format('Y').'-'.$selected->format('m').'-'.$start->format('d')))->modify('+1 month')->modify('-1 day')->modify('-1 month');
        $prev_pay_date = (new DateTime($selected->format('Y').'-'.$selected->format('m').'-'.$start_2->format('d')))->modify('+'.$end_2->diff($paydate)->format("%r%a").' day');

        if($selected >= $start_2 && $selected <= $end_2) {
            $arrange = [
                'start_date' => $start_2->format('Y-m-d'),
                'end_date' => $end_2->format('Y-m-d'),
                'pay_date' => $paydate->format('Y-m-d')
            ];

            $timesheet = TimeLogApprovals::where('start_date','>=', $start_2->format('Y-m-d'))->count();

        }
        else if($selected >= $new_start && $selected <= $new_end){
            $arrange = [
                'start_date' => $new_start->format('Y-m-d'),
                'end_date' => $new_end->format('Y-m-d'),
                'pay_date' => $new_pay_date->format('Y-m-d')
            ];

            $timesheet = TimeLogApprovals::where('start_date','>=', $new_start->format('Y-m-d'))->count();
        }
        else {
            $arrange = [
                'start_date' => $prev_start->format('Y-m-d'),
                'end_date' => $prev_end->format('Y-m-d'),
                'pay_date' => $prev_pay_date->format('Y-m-d')
            ];

            $timesheet = TimeLogApprovals::where('start_date','>=', $new_start->format('Y-m-d'))->count();
         
        }
        

        return ["arrange"=>$arrange, "timesheet"=>$timesheet];
    }

    public function get_calendar($type) {
        $calendar = PayrollCalendar::where('type', $type)->get();

        return response()->json(compact('calendar'));
    }
    
}
