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
use ZKLib\ZKLib as LegacyZKLib;
use Rats\Zkteco\Lib\ZKTeco as RatsZKTeco;


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
        $payrollGroup = request()->query('payroll_group', 'all');

        if(request()->ajax()) {
            $record = EmployeeInformation::selectRaw($script)
                ->leftJoin('time_logs', 'employees.id', '=', 'time_logs.employee_id')
                ->leftJoin('earnings', 'earnings.id', '=', 'time_logs.type')
                ->leftJoin('employments', 'employees.id', '=', 'employments.employee_id')
                ->leftJoin('payroll_calendars', 'payroll_calendars.id', '=', 'employments.payroll_calendar_id')
                ->whereHas('employments_tab', function($query) {
                    $query;
                })->with(['approval' => function($query) use($first, $last) {
                    $query->where('start_date', '<=', $last)
                        ->where('end_date', '>=', $first);
                }])

                ->when($department !== "all", function($query) use ($department) {
                    $query->where('employments.department_id', '=', $department);
                })
                ->when($payrollGroup !== "all" && !empty($payrollGroup), function($query) use ($payrollGroup) {
                    $query->where('employees.employment_type', '=', $payrollGroup);
                })
                ->groupBy("employees.id")
                ->get();

            return datatables()->of(
                $record
            )
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function sync_device(Request $request)
    {
        $ip = env('ZKTECO_DEVICE_IP', '192.168.68.117');
        $port = (int) env('ZKTECO_DEVICE_PORT', 4370);
        $synced = 0;
        $skipped = 0;
        $unchanged = 0;
        $invalid = 0;

        try {
            $attendanceLogs = $this->fetchDeviceAttendanceLogs($ip, $port);

            if ($attendanceLogs === null) {
                return response()->json([
                    'message' => 'Unable to connect to the device.',
                ], 422);
            }

            $employeesByDeviceUserId = EmployeeInformation::whereNotNull('device_user_id')
                ->get()
                ->keyBy(function ($employee) {
                    return (string) $employee->device_user_id;
                });

            $mappedAttendance = collect();

            foreach ($attendanceLogs as $attendance) {
                $deviceUserId = (string) $attendance['device_user_id'];
                $employee = $employeesByDeviceUserId->get($deviceUserId);

                if ($employee === null) {
                    $skipped++;
                    continue;
                }

                $logDateTime = $attendance['datetime'];

                if ((int) $logDateTime->format('Y') <= 2000) {
                    $invalid++;
                    continue;
                }

                $mappedAttendance->push([
                    'employee' => $employee,
                    'datetime' => clone $logDateTime,
                ]);
            }

            $mappedAttendance
                ->groupBy(function ($entry) {
                    return $entry['employee']->id . '|' . $entry['datetime']->format('Y-m-d');
                })
                ->each(function ($entries) use (&$synced, &$unchanged) {
                    $employee = $entries->first()['employee'];
                    $punches = $entries->pluck('datetime')->all();
                    $changed = $this->syncAttendanceToTimeLog($employee, $punches);

                    if ($changed) {
                        $synced++;
                    } else {
                        $unchanged++;
                    }
                });

            return response()->json([
                'message' => 'Device sync completed.',
                'synced' => $synced,
                'skipped' => $skipped,
                'unchanged' => $unchanged,
                'invalid' => $invalid,
                'device_ip' => $ip,
                'device_port' => $port,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Device sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function fetchDeviceAttendanceLogs($ip, $port)
    {
        $logs = $this->fetchAttendanceWithRatsLibrary($ip, $port);

        if ($logs !== null) {
            return $logs;
        }

        return $this->fetchAttendanceWithLegacyLibrary($ip, $port);
    }

    private function fetchAttendanceWithRatsLibrary($ip, $port)
    {
        try {
            $zk = new RatsZKTeco($ip, $port);

            if (! $zk->connect()) {
                return null;
            }

            $logs = collect($zk->getAttendance())
                ->map(function ($log) {
                    if (empty($log['timestamp'])) {
                        return null;
                    }

                    try {
                        return [
                            // Prefer the human/device ID string shown by the device.
                            'device_user_id' => isset($log['id']) ? trim((string) $log['id']) : (string) ($log['uid'] ?? ''),
                            'uid' => isset($log['uid']) ? (string) $log['uid'] : null,
                            'datetime' => new DateTime($log['timestamp']),
                            'raw' => $log,
                        ];
                    } catch (\Throwable $e) {
                        return null;
                    }
                })
                ->filter()
                ->sortBy(function ($log) {
                    return $log['datetime']->format('Y-m-d H:i:s');
                })
                ->values();

            $zk->disconnect();

            return $logs;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function fetchAttendanceWithLegacyLibrary($ip, $port)
    {
        try {
            $zk = new LegacyZKLib($ip, $port);
            $connected = $zk->connect();

            if (! $connected) {
                return null;
            }

            $logs = collect($zk->getAttendance())
                ->map(function ($attendance) {
                    return [
                        'device_user_id' => (string) $attendance->getUserId(),
                        'uid' => (string) $attendance->getUserId(),
                        'datetime' => $attendance->getDateTime(),
                        'raw' => $attendance,
                    ];
                })
                ->sortBy(function ($attendance) {
                    return $attendance['datetime']->format('Y-m-d H:i:s');
                })
                ->values();

            $zk->disconnect();

            return $logs;
        } catch (\Throwable $e) {
            return null;
        }
    }
    
    public function save(Request $request)
    {  
        $activity = new ActivityController();

        foreach($request->record as $record) {
            $record = $this->normalizeTimeLogRecordForSave($record);

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

    private function normalizeTimeLogRecordForSave(array $record)
    {
        $record['time_in'] = $this->normalizeSameDayDateTime($record['date'] ?? null, $record['time_in'] ?? null);
        $record['break_in'] = $this->normalizeSameDayDateTime($record['date'] ?? null, $record['break_in'] ?? null);
        $record['break_out'] = $this->normalizeSameDayDateTime($record['date'] ?? null, $record['break_out'] ?? null);
        $record['ot_in'] = $this->normalizeSameDayDateTime($record['date'] ?? null, $record['ot_in'] ?? null);
        $record['ot_out'] = $this->normalizeSameDayDateTime($record['date'] ?? null, $record['ot_out'] ?? null);
        $record['time_out'] = $this->normalizeTimeoutDateTime(
            $record['date'] ?? null,
            $record['time_out'] ?? null,
            !empty($record['is_next_day_timeout'])
        );

        unset($record['is_next_day_timeout']);

        return $record;
    }

    private function normalizeSameDayDateTime($workDate, $value)
    {
        if (empty($workDate) || empty($value)) {
            return null;
        }

        $valueString = trim((string) $value);

        if (preg_match('/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}(:\d{2})?$/', $valueString)) {
            return strlen($valueString) === 16 ? $valueString . ':00' : $valueString;
        }

        if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $valueString)) {
            return $workDate . ' ' . (strlen($valueString) === 5 ? $valueString . ':00' : $valueString);
        }

        return $valueString;
    }

    private function normalizeTimeoutDateTime($workDate, $value, $isNextDay)
    {
        if (empty($workDate) || empty($value)) {
            return null;
        }

        $valueString = trim((string) $value);

        if (preg_match('/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}(:\d{2})?$/', $valueString)) {
            $dateTime = new DateTime(strlen($valueString) === 16 ? $valueString . ':00' : $valueString);

            if ($isNextDay && $dateTime->format('Y-m-d') === $workDate) {
                $dateTime->modify('+1 day');
            }

            return $dateTime->format('Y-m-d H:i:s');
        }

        if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $valueString)) {
            $dateTime = new DateTime($workDate . ' ' . (strlen($valueString) === 5 ? $valueString . ':00' : $valueString));
            if ($isNextDay) {
                $dateTime->modify('+1 day');
            }

            return $dateTime->format('Y-m-d H:i:s');
        }

        return $valueString;
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
        $rangeStart = $request->start_date ?: $request->date;
        $rangeEnd = $request->end_date ?: $request->date;

        try {
            $record = EmployeeInformation::with(['compensations','employments_tab', 'employments_tab.positions', 'employments_tab.departments', 'employments_tab.calendar', 'approval' => function($query) use($rangeStart, $rangeEnd) {
                $query->where('start_date', '<=', $rangeEnd)->where('end_date', '>=', $rangeStart);
            }])->where('id', $id)->first();

            $record = $this->applySalaryAdjustment($record, $rangeEnd);

            if($record->employments_tab !== null) {
                $details = $this->generateCustomRangeOutput($rangeStart, $rangeEnd, $id);
            }
            else {
                $details = null;
            }

            return response()->json(compact('record', 'details', 'ot_earning', 'rangeStart', 'rangeEnd'));
        }
        catch(ErrorException $e) {
            return response()->json(['error' => 'Record not found'], 404);
        }
    }

    public function generateCustomRangeOutput($startDate, $endDate, $id) {
        $output = [];
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod(new DateTime($startDate), $interval, (new DateTime($endDate))->modify('+1 day'));

        foreach($period as $dt) {
            try {
                $leave = LeaveRequest::with('leave_type')
                    ->where('employee_id', $id)
                    ->where('start_date', '<=', $dt->format('Y-m-d'))
                    ->where('end_date', '>=', $dt->format('Y-m-d'))
                    ->first();

                $overtime = OvertimeRequest::where('employee_id', $id)
                    ->where('ot_date', '=', $dt->format('Y-m-d'))
                    ->where('status', 'approved')
                    ->first();

                $employee = Timelogs::where('employee_id', $id)
                    ->where('date', $dt->format('Y-m-d'))
                    ->first();

                $calendar = WorkCalendar::where('employee_id', $id)->first();
                $holiday = Holiday::whereMonth('date', $dt->format('m'))
                    ->whereDay('date', $dt->format('d'))
                    ->first();

                $status = $calendar !== null ? (
                    $calendar[strtolower($dt->format('l'))."_start_time"] !== null ? (
                        $holiday !== null ? "HOLIDAY" : (
                            $leave !== null ? $leave->leave_type->leave_name : (
                                $employee !== null ? "WORK" : "ABSENT"
                            )
                        )
                    ) : "OFF"
                ) : "OFF";

                array_push($output, [
                    "date" => $dt->format('Y-m-d'),
                    "day" => $dt->format('l'),
                    "status" => $status,
                    "time_in" => $employee !== null ? $employee->time_in : null,
                    "break_in" => $employee !== null ? $employee->break_in : null,
                    "break_out" => $employee !== null ? $employee->break_out : null,
                    "time_out" => $employee !== null ? $employee->time_out : null,
                    "ot_in" => $employee !== null ? $employee->ot_in : null,
                    "ot_out" => $employee !== null ? $employee->ot_out : null,
                    "office_hours" => $employee !== null ? ($employee->time_in !== null && $employee->time_out !== null ? $this->countHours($employee->time_in, $employee->time_out) : "0.00") : "0.00",
                    "break_time" => $employee !== null ? ($employee->time_in !== null && $employee->time_out !== null ? "1.00" : "0.00") : "0.00",
                    "overtime" => $overtime !== null ? number_format((float) $overtime->total_hours, 2, '.', '') : "0.00",
                ]);
            } catch(Exception $e) {
                array_push($output, [
                    "date" => $dt->format('Y-m-d'),
                    "day" => $dt->format('l'),
                    "status" => "-",
                    "time_in" => null,
                    "break_in" => null,
                    "break_out" => null,
                    "time_out" => null,
                    "ot_in" => null,
                    "ot_out" => null,
                    "office_hours" => "0.00",
                    "break_time" => "0.00",
                    "overtime" => "0.00",
                ]);
            }
        }

        return $output;
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

    private function syncAttendanceToTimeLog(EmployeeInformation $employee, array $punches)
    {
        $normalizedPunches = $this->normalizeDevicePunches($punches);

        if (count($normalizedPunches) === 0) {
            return false;
        }

        $date = $normalizedPunches[0]->format('Y-m-d');
        $timeLog = TimeLogs::firstOrNew([
            'employee_id' => $employee->id,
            'date' => $date,
        ]);

        $isNew = ! $timeLog->exists;
        $mapped = $this->mapPunchesToTimeLogFields($normalizedPunches);

        if ($isNew) {
            $timeLog->type = $timeLog->type ?: 1;
            $timeLog->status = 0;
            $timeLog->schedule_status = $timeLog->schedule_status ?: 2;
            $timeLog->workstation_id = Auth::user()->workstation_id;
            $timeLog->created_by = Auth::user()->id;
            $timeLog->updated_by = Auth::user()->id;
            $timeLog->log_type = 'device';
        }

        $payload = array_merge($mapped, [
            'break_hours' => $this->calculateHoursBetween($mapped['break_out'], $mapped['break_in']),
            'ot_hours' => $this->calculateHoursBetween($mapped['ot_in'], $mapped['ot_out']),
            'late_hours' => 0,
            'undertime' => 0,
            'log_type' => 'device',
        ]);

        $payload['total_hours'] = $this->calculateRegularHours(
            $mapped['time_in'],
            $mapped['time_out'],
            $payload['break_hours']
        );

        $changed = false;

        foreach ($payload as $field => $value) {
            if ((string) $timeLog->{$field} !== (string) $value) {
                $timeLog->{$field} = $value;
                $changed = true;
            }
        }

        if ($changed || $isNew) {
            $timeLog->updated_by = Auth::user()->id;
            $timeLog->save();

            return true;
        }

        return false;
    }

    private function normalizeDevicePunches(array $punches)
    {
        usort($punches, function ($left, $right) {
            return $left <=> $right;
        });

        $normalized = [];
        $clusterStart = null;

        foreach ($punches as $punch) {
            if (! $punch instanceof DateTime) {
                continue;
            }

            if ($clusterStart === null) {
                $clusterStart = clone $punch;
                $normalized[] = clone $punch;
                continue;
            }

            $diffInSeconds = abs($punch->getTimestamp() - $clusterStart->getTimestamp());

            // Collapse one burst of repeated scans into a single punch.
            if ($diffInSeconds <= 300) {
                continue;
            }

            $clusterStart = clone $punch;
            $normalized[] = clone $punch;
        }

        return $normalized;
    }

    private function mapPunchesToTimeLogFields(array $punches)
    {
        $timestamps = array_map(function (DateTime $punch) {
            return $punch->format('Y-m-d H:i:s');
        }, array_values($punches));

        $count = count($timestamps);

        if ($count === 1) {
            return [
                'time_in' => $timestamps[0],
                'break_out' => null,
                'break_in' => null,
                'time_out' => null,
                'ot_in' => null,
                'ot_out' => null,
            ];
        }

        if ($count === 2) {
            return [
                'time_in' => $timestamps[0],
                'break_out' => null,
                'break_in' => null,
                'time_out' => $timestamps[1],
                'ot_in' => null,
                'ot_out' => null,
            ];
        }

        if ($count === 3) {
            return [
                'time_in' => $timestamps[0],
                'break_out' => $timestamps[1],
                'break_in' => null,
                'time_out' => $timestamps[2],
                'ot_in' => null,
                'ot_out' => null,
            ];
        }

        return [
            'time_in' => $timestamps[0] ?? null,
            'break_out' => $timestamps[1] ?? null,
            'break_in' => $timestamps[2] ?? null,
            'time_out' => $timestamps[3] ?? null,
            'ot_in' => $timestamps[4] ?? null,
            'ot_out' => $timestamps[5] ?? null,
        ];
    }

    private function calculateHoursBetween($start, $end)
    {
        if ($start === null || $end === null) {
            return 0;
        }

        $seconds = strtotime($end) - strtotime($start);

        if ($seconds <= 0) {
            return 0;
        }

        return number_format($seconds / 3600, 2, '.', '');
    }

    private function calculateRegularHours($timeIn, $timeOut, $breakHours)
    {
        if ($timeIn === null || $timeOut === null) {
            return 0;
        }

        $seconds = strtotime($timeOut) - strtotime($timeIn);

        if ($seconds <= 0) {
            return 0;
        }

        $hours = ($seconds / 3600) - (float) $breakHours;

        return number_format(max($hours, 0), 2, '.', '');
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
