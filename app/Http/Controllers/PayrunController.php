<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Carbon\Carbon;
use App\AllowanceSetup;
use App\CashAdvance;
use App\Holiday;
use App\Employment;
use App\PayrollSummary;
use App\PayrollSummaryDetails;
use App\EmployeeInformation;
use App\PayrollCalendar;
use App\OvertimeRequest;
use App\Earnings;
use App\TimeLogs;
use App\Allowance;
use App\LeaveRequest;
use App\Classes\Computation\Payroll\WithholdingTax as WithholdingTax_Benefits;

class PayrunController extends Controller
{
    protected $withholding_tax;

    public function __construct() 
    {
        $this->withholding_tax = new WithholdingTax_Benefits();
    }

    public function index() {
        $calendar = PayrollCalendar::get();
        $allowance = Allowance::get();
        return view('backend.pages.payroll.transaction.payrun.index', compact('calendar', 'allowance'), ["type"=>"full-view"]);
    }

    public function get(Request $request) {
        if(request()->ajax()) {
            $summary = PayrollSummary::selectRaw('payroll_summaries.id,
                        payroll_calendars.title,
                        payroll_summaries.sequence_no,
                        payroll_summaries.schedule_type,
                        payroll_summaries.period_start,
                        payroll_summaries.payroll_period,
                        SUM(CASE WHEN payroll_summary_details.status = 1 THEN 1 ELSE 0 END) no_of_employee,
                        SUM(CASE WHEN payroll_summary_details.deleted_at IS NULL THEN 1 ELSE 0 END) AS total_of_employee,
                        SUM(CASE WHEN payroll_summary_details.status = 1 THEN payroll_summary_details.gross_earnings ELSE 0 END) amount,
                        payroll_summaries.status')
                        ->leftJoin('payroll_summary_details', 'payroll_summary_details.summary_id', '=', 'payroll_summaries.id')
                        ->join('payroll_calendars', 'payroll_summaries.sequence_title', '=', 'payroll_calendars.id');
    
            if ($request->status !== null && $request->status !== '') {
                $summary->where('payroll_summaries.status', (int)$request->status);
            }
    
            if ($request->schedule_type !== null && $request->schedule_type !== '') {
                $summary->where('payroll_summaries.schedule_type', (int)$request->schedule_type);
            }
    
            if (($request->start_date !== null && $request->start_date !== '') || ($request->end_date !== null && $request->end_date !== '')) {
                try {
                    $start = $request->start_date ? Carbon::parse($request->start_date)->toDateString() : null;
                    $end = $request->end_date ? Carbon::parse($request->end_date)->toDateString() : null;
    
                    if ($start && $end) {
                        $summary->where('payroll_summaries.period_start', '>=', $start)
                                ->where('payroll_summaries.period_start', '<=', $end);
                    } elseif ($start) {
                        $summary->where('payroll_summaries.period_start', '>=', $start);
                    } elseif ($end) {
                        $summary->where('payroll_summaries.period_start', '<=', $end);
                    }
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Invalid date format'], 400);
                }
            }
    
            $summary = $summary->groupBy('payroll_summaries.id', 
                        'payroll_calendars.title', 
                        'payroll_summaries.sequence_no', 
                        'payroll_summaries.schedule_type', 
                        'payroll_summaries.period_start', 
                        'payroll_summaries.payroll_period', 
                        'payroll_summaries.status');
    
            return datatables()->of($summary->get())
                ->addIndexColumn()
                ->make(true);
        }
    }

    public function getDetails(Request $request)
    {
        $details = PayrollSummaryDetails::with([
            'employee',
            'header',
            'schedule',
            'employee.compensations',
            'employee.employments_tab.calendar',
            'timelogs' => function ($query) use ($request) {
                $query->whereBetween('date', [$request->start, $request->end])->whereNotNull('time_in');
            },
            'ot_request' => function ($query) use ($request) {
                $query->where('status', 'approved')->whereBetween('ot_date', [$request->start, $request->end]);
            },
            'leave_request' => function ($query) use ($request) {
                $query->where('status', 1)->where(function ($q) use ($request) {
                    $q->whereBetween('start_date', [$request->start, $request->end])
                        ->orWhereBetween('end_date', [$request->start, $request->end])
                        ->orWhere(function ($q) use ($request) {
                            $q->where('start_date', '<', $request->end)
                                ->where('end_date', '>', $request->start);
                        });
                });
            }
        ])
        ->whereHas('employee')
        ->where('summary_id', $request->id)
        ->get()
        ->map(function ($details) use ($request) {
            $sep = 1;
            $type = $details->employee->employments_tab->calendar->type;
            $employmentType = $details->employee->employment_type;
            $isFixedRate = $employmentType === 'fixed_rate';

            if ($type === 2 || $type === 3) {
                $sep = 2;
            } elseif ($type === 4) {
                $sep = 4;
            }

            if (!$details->schedule || !$details->timelogs) {
                $details->late_minutes = 0.00;
                $details->absent_count = 0;
                return $details;
            }

            $totalLateMinutes = 0;
            $totalUnderTime = 0;
            $totalOTMinutes = 0;
            $absentCount = 0;
            $leaveCount = 0;
            $timelogDates = collect($details->timelogs)->pluck('date')->toArray();

            $startDate = \Carbon\Carbon::parse($request->start);
            $endDate = \Carbon\Carbon::parse($request->end);

            if (!$isFixedRate) {
                for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                    $dayOfWeek = strtolower($date->format('l'));
                    if (!empty($details->schedule[$dayOfWeek . '_start_time'])) {
                        if (!in_array($date->toDateString(), $timelogDates)) {
                            $absentCount++;
                        }
                    }
                }
            }

            if (!$isFixedRate) {
                foreach ($details->timelogs as $timelog) {
                    if ($timelog->time_in) {
                        $timelogDate = \Carbon\Carbon::parse($timelog->date);
                        $dayOfWeek = strtolower($timelogDate->format('l'));

                        $expectedStart = \Carbon\Carbon::parse($timelog->date . " " . $details->schedule[$dayOfWeek . '_start_time']);
                        $actualClockIn = \Carbon\Carbon::parse($timelog->time_in);

                        if ($actualClockIn->greaterThan($expectedStart)) {
                            $lateMinutes = $actualClockIn->diffInMinutes($expectedStart);
                            $totalLateMinutes += $lateMinutes;
                        }
                    }

                    if ($timelog->time_out) {
                        $timelogDate = \Carbon\Carbon::parse($timelog->date);
                        $dayOfWeek = strtolower($timelogDate->format('l'));

                        $expectedEnd = \Carbon\Carbon::parse($timelog->date . " " . $details->schedule[$dayOfWeek . '_end_time']);
                        $actualClockOut = \Carbon\Carbon::parse($timelog->time_out);

                        if ($actualClockOut->lessThan($expectedEnd)) {
                            $lateUnder = $actualClockOut->diffInMinutes($expectedEnd);
                            $totalUnderTime += $lateUnder;
                        }
                    }
                }
            }

            foreach ($details->ot_request as $ot) {
                if ($ot->start_time) {
                    $startTime = \Carbon\Carbon::parse($ot->start_time);
                    $endTime = \Carbon\Carbon::parse($ot->end_time);

                    $otMinutes = $startTime->diffInMinutes($endTime);
                    $totalOTMinutes += $otMinutes;
                }
            }

            foreach ($details->leave_request as $leave) {
                $leaveCount += $leave->total_leave_hours;
            }

            $ot_multiplier = Earnings::where('code', 'OT')->first();

            $details->late_hours = round($totalLateMinutes, 2);
            $details->undertime = round($totalUnderTime, 2);
            $details->ot_hours = round($totalOTMinutes / 60, 2) >= 1 ? round($totalOTMinutes / 60, 2) : 0;
            $details->ot_amount = (floatval($details->employee->compensations->hourly_salary) * $details->ot_hours) * $ot_multiplier->multiplier;
            $details->leave_count = $leaveCount;
            $details->sep = $sep;
            $details->absent_count = $absentCount - $leaveCount;

            $details->ca = CashAdvance::where('employee_id', $details->employee_id)
                ->where('summary_id', $details->summary_id)
                ->sum('amount');

            // === HOLIDAY LOGIC (UPDATED) ===
            $allHolidays = Holiday::with('holiday_type')
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->get();

            $validHolidays = $allHolidays->filter(function ($holiday) use ($details) {
                $dayOfWeek = strtolower(\Carbon\Carbon::parse($holiday->date)->format('l'));
                return !empty($details->schedule[$dayOfWeek . '_start_time']);
            });

            if (!$isFixedRate) {
                $details->holiday = $validHolidays->count();
                $details->holiday_data = $validHolidays->values();
            }
            else {
                $details->holiday = 0;
                $details->holiday_data = null;
            }

            // Allowance
            $details->allowance_days = AllowanceSetup::where('employee_id', $details->employee_id)
                ->where('sequence_no', $details->summary_id)
                ->sum('days');
            $details->allowance_amount = AllowanceSetup::where('employee_id', $details->employee_id)
                ->where('sequence_no', $details->summary_id)
                ->sum('amount');

            // Rates
            $daily_rate = $details->daily !== "0" ? floatval($details->daily) : ($details->employee->compensations !== null ? $details->employee->compensations->daily_salary : 0);
            $hourly_rate = $details->hourly !== "0" ? floatval($details->hourly) : ($details->employee->compensations !== null ? $details->employee->compensations->hourly_salary : 0);
            $monthly_rate = $details->monthly !== "0" ? floatval($details->monthly) : ($details->employee->compensations !== null ? $details->employee->compensations->monthly_salary : 0);

            if ($isFixedRate) {
                $worked_days = 0;
                for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                    $dayOfWeek = strtolower($date->format('l'));
                    if (!empty($details->schedule[$dayOfWeek . '_start_time'])) {
                        $worked_days++;
                    }
                }

                $details->for_fixed = $worked_days;
                $absentCount = 0;
            } else {
                $worked_days = intval(count($details->timelogs));
                $details->for_fixed = null;
            }

            $leave_amount = floatval($details->leave_count * $daily_rate);

            $holiday_rate = 0;
            if($details->holiday_data !== null) {
                foreach ($details->holiday_data as $h_item) {
                    $holiday_rate += $daily_rate * floatval($h_item->holiday_type->multiplier);
                }
            }

            $late = floatval($details->late_hours);
            $late_rate = ($late / 60) * $hourly_rate;

            $absent = $details->absent_count;
            $absent_rate = $absent * $daily_rate;
            $tardiness_deduct = $absent_rate === 0 ? $late_rate : ($absent_rate - $late_rate);

            $gross_salary = (
                ($type === 2 ? ($monthly_rate / 2) : ($worked_days * $daily_rate))
                + floatval($details->ot_amount)
                + floatval($details->allowance_amount)
                + $leave_amount
                + $holiday_rate
            ) - $tardiness_deduct;

            $details->tax_final = $this->getTax($type, $gross_salary);

            return $details;
        });

        return response()->json(compact('details'));
    }


    public function save(Request $request) {
        
        $code = '';
        $sep = 1;

        switch($request->payment_schedule) {
            case '1':
                $code = 'W';
                $sep = 1;
                break;

            case '2':
                $code = 'S';
                $sep = 2;
                break;

            case '3':
                $code = 'M';
                $sep = 2;
                break;
                
            case '4':
                $code = 'W';
                $sep = 4;
                break;
        };

        $data = array(
            "sequence_no" => $code."-".date('mdY', strtotime($request->payroll_period)),
            "sequence_title" => $request->sequence_title,
            "schedule_type" => $request->payment_schedule,
            "period_start" => $request->period_start,
            "payroll_period" => $request->payroll_period,
            "pay_date" => $request->pay_date,
            "status" => 0,
            "workstation_id" => Auth::user()->workstation_id,
            "created_by" => Auth::user()->id,
            "updated_by" => Auth::user()->id,
        );

        $summary = PayrollSummary::where('sequence_title', $request->sequence_title)->where('period_start', $request->period_start)->where('payroll_period', $request->payroll_period)->count();

        if($summary === 0) {
            $record = PayrollSummary::create($data);
    
            $employments = Employment::join('payroll_calendars', 'payroll_calendars.id', '=', 'employments.payroll_calendar_id')
                ->join('employees', 'employees.id', '=', 'employments.employee_id')
                ->join('compensations', 'employees.id', '=', 'compensations.employee_id')
                ->where('payroll_calendar_id', $request->sequence_title)
                ->get();
            
            
            foreach($employments as $item) {

                $details = array(
                    "employee_id" => $item->employee_id,
                    "sequence_no" => $code."-".date('mdY', strtotime($request->payroll_period)),
                    "summary_id" => $record->id,
                    "gross_earnings" => 0,
                    "sss" => $item->sss / $sep,
                    "pagibig" => $item->pagibig / $sep,
                    "philhealth" => $item->phic / $sep,
                    "daily" => $item->daily_salary,
                    "monthly" => $item->monthly_salary,
                    "hourly" => $item->hourly_salary,
                    "tax" => 0,
                    "net_pay" => 0,
                    "status" => 0,
                    "workstation_id" => Auth::user()->workstation_id,
                    "created_by" => Auth::user()->id,
                    "updated_by" => Auth::user()->id,
                );
    
                PayrollSummaryDetails::create($details);
            }

            // return response()->json(["sample" => $tax]);
        }
        else {
            return response()->json(['responseJSON' => ["message" => "Payrun is already exist."]], 500);
        }
    }

    public function getEmployeeDetails(Request $request) {
        $employee = EmployeeInformation::with(['compensations', 'works_calendar', 'timelogs' => function($query) use($request) {
            $query->whereBetween('date', [$request->start, $request->end]);
        }])->where('id', $request->employee_id)->first();
        $summary = PayrollSummaryDetails::where('id', $request->id)->first();
        $holiday = Holiday::whereBetween('date', [$request->start, $request->end])->get();

        $leave = LeaveRequest::where('status', 1)->where('employee_id', $request->employee_id)->where(function($q) use ($request) {
            $q->whereBetween('start_date', [$request->start, $request->end])
                ->orWhereBetween('end_date', [$request->start, $request->end])
                ->orWhere(function($q) use ($request) {
                    $q->where('start_date', '<', $request->end)
                    ->where('end_date', '>', $request->start);
                });
        })->get();

        return response()->json(compact('employee', 'summary', 'holiday', 'leave'));
    }

    public function getSchedType($id) {
        $calendar = PayrollCalendar::where('id', $id)->first();

        return response()->json(compact('calendar'));
    }
    
    public function getOTList(Request $request) {
        $ot = OvertimeRequest::where('employee_id', $request->employee_id)->whereBetween('ot_date', [$request->start, $request->end])->get();

        return response()->json(compact('ot'));
    }

    public function saveUpdate(Request $request) {

        foreach($request->data as $item) {
            if($item['id'] !== "null") {
                TimeLogs::where('id', $item['id'])->update([
                    "time_in" => $item['time_in'] !== "" && $item['time_in'] !== null?$item['date']." ".$item['time_in']:null, 
                    "time_out" => $item['time_out'] !== "" && $item['time_out'] !== null?$item['date']." ".$item['time_out']:null, 
                    "break_in" => $item['break_in'] !== "" && $item['break_in'] !== null?$item['date']." ".$item['break_in']:null, 
                    "break_out" => $item['break_out'] !== "" && $item['break_out'] !== null?$item['date']." ".$item['break_out']:null
                ]);
            }
            else {
                if($item['time_in'] !== "" && $item['time_in'] !== null) {
                    $data = array(
                        "employee_id" => $request->emp_id,
                        "date" => $item['date'],
                        "time_in" => $item['time_in'] !== "" && $item['time_in'] !== null?$item['date']." ".$item['time_in']:null, 
                        "time_out" => $item['time_out'] !== "" && $item['time_out'] !== null?$item['date']." ".$item['time_out']:null, 
                        "break_in" => $item['break_in'] !== "" && $item['break_in'] !== null?$item['date']." ".$item['break_in']:null, 
                        "break_out" => $item['break_out'] !== "" && $item['break_out'] !== null?$item['date']." ".$item['break_out']:null,
                        "total_hours" => 0,
                        "break_hours" => 0,
                        "ot_hours" => 0,
                        "late_hours" => 0,
                        "undertime" => 0,
                        "type" => 1,
                        "status" => 0,
                        "workstation_id" => Auth::user()->workstation_id,
                        "created_by" => Auth::user()->id,
                        "updated_by" => Auth::user()->id,
                    );

                    TimeLogs::create($data);
                }

            }
        }

    }

    public function deleteRecord(Request $request) { 
        PayrollSummary::where('id', $request->id)->delete();
        PayrollSummaryDetails::where('summary_id', $request->id)->delete();
    }

    public function edit($id) {
        $payrun = PayrollSummary::where('id', $id)->first();

        return response()->json(compact('payrun'));
    }
    
    public function update(Request $request, $id) {
        
        $code = '';
        $sep = 1;

        switch($request->payment_schedule) {
            case '1':
                $code = 'W';
                $sep = 1;
                break;

            case '2':
                $code = 'S';
                $sep = 2;
                break;

            case '3':
                $code = 'M';
                $sep = 2;
                break;
                
            case '4':
                $code = 'W';
                $sep = 4;
                break;
        };

        $data = array(
            "sequence_no" => $code."-".date('mdY', strtotime($request->payroll_period)),
            "sequence_title" => $request->sequence_title,
            "schedule_type" => $request->payment_schedule,
            "period_start" => $request->period_start,
            "payroll_period" => $request->payroll_period,
            "pay_date" => $request->pay_date,
            "status" => 0,
            "workstation_id" => Auth::user()->workstation_id,
            "created_by" => Auth::user()->id,
            "updated_by" => Auth::user()->id,
        );

        $record = PayrollSummary::where('id', $id)->update($data);
        // PayrollSummaryDetails::where('summary_id', $id)->delete();

        $employments = Employment::join('payroll_calendars', 'payroll_calendars.id', '=', 'employments.payroll_calendar_id')
            ->join('employees', 'employees.id', '=', 'employments.employee_id')
            ->join('compensations', 'employees.id', '=', 'compensations.employee_id')
            ->where('payroll_calendar_id', $request->sequence_title)
            ->get();
            
        
        foreach($employments as $item) {

            $details = array(
                "employee_id" => $item->employee_id,
                "sequence_no" => $code."-".date('mdY', strtotime($request->payroll_period)),
                "summary_id" => $id,
                "gross_earnings" => 0,
                "sss" => $item->sss / $sep,
                "pagibig" => $item->pagibig / $sep,
                "philhealth" => $item->phic / $sep,
                "tax" => 0,
                "net_pay" => 0,
                "status" => 0,
                "workstation_id" => Auth::user()->workstation_id,
                "created_by" => Auth::user()->id,
                "updated_by" => Auth::user()->id,
            );

            if(PayrollSummaryDetails::where('summary_id', $id)->where('employee_id', $item->employee_id)->first()) {
                PayrollSummaryDetails::where('summary_id', $id)->where('employee_id', $item->employee_id)->update($details);
            }
            else{
                PayrollSummaryDetails::create($details);
            }

        }
        
        // return response()->json(["sample" => $tax]);
    }

    public function approveDetails(Request $request) {
        PayrollSummaryDetails::where('id', $request->id)->update(['status' => 1, 'updated_by' => Auth::user()->id]);
    }

    public function crossDetails(Request $request) {
        PayrollSummaryDetails::where('id', $request->id)->update(['status' => 0, 'updated_by' => Auth::user()->id]);
    }

    public function updateAmount(Request $request, $id) {
        PayrollSummaryDetails::where('id', $id)->update([$request->cell => $request->amount, 'updated_by' => Auth::user()->id]);
    }

    public function getTax($frequency, $gross) {
        $tax = $this->withholding_tax->getValue($gross, $frequency);

        // switch($frequency) {
        //     case '1':
        //         $sep = 1;
        //         break;

        //     case '2':
        //         $sep = 2;
        //         break;

        //     case '3':
        //         $sep = 2;
        //         break;
                
        //     case '4':
        //         $sep = 4;
        //         break;
        // };
        
        return ($tax !== null?floatval((($gross - $tax->range_from)*($tax->rate_on_excess*0.01))+$tax->fix_tax):0);
    }

    public function getDetailsInfo(Request $request) {
        $details = PayrollSummaryDetails::with([
            'employee', 'header', 'schedule', 'employee.compensations',
            'employee.employments_tab.calendar', 'employee.employments_tab.departments',
            'employee.employments_tab.positions', 'timelogs' => function ($query) use ($request) {
                $query->whereBetween('date', [$request->start, $request->end])->whereNotNull('time_in');
            },
            'ot_request' => function ($query) use ($request) {
                $query->where('status', 'approved')->whereBetween('ot_date', [$request->start, $request->end]);
            },
            'leave_request' => function ($query) use ($request) {
                $query->where('status', 1)->where(function($q) use ($request) {
                    $q->whereBetween('start_date', [$request->start, $request->end])
                        ->orWhereBetween('end_date', [$request->start, $request->end])
                        ->orWhere(function($q) use ($request) {
                            $q->where('start_date', '<', $request->end)
                                ->where('end_date', '>', $request->start);
                        });
                });
            }
        ])->whereHas('employee')->where('id', $request->id)->get()
            ->map(function ($details) use ($request) {
                $sep = 1;
                $type = $details->employee->employments_tab->calendar->type;
                $employmentType = $details->employee->employment_type;
                $isFixedRate = $employmentType === 'fixed_rate';
                
                $sep = in_array($type, [2, 3]) ? 2 : ($type === 4 ? 4 : 1);

                if (!$details->schedule || !$details->timelogs) {
                    $details->late_minutes = 0.00;
                    $details->absent_count = 0;
                    return $details;
                }

                $totalLateMinutes = 0;
                $totalUnderTime = 0;
                $totalOTMinutes = 0;
                $absentCount = 0;
                $leaveCount = 0;
                $timelogDates = collect($details->timelogs)->pluck('date')->toArray();

                $startDate = Carbon::parse($request->start);
                $endDate = Carbon::parse($request->end);
                $h_start = $startDate->format('m-d');
                $h_end = $endDate->format('m-d');

                if (!$isFixedRate) {
                    for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                        $dayOfWeek = strtolower($date->format('l'));
                        if (!empty($details->schedule[$dayOfWeek . '_start_time'])) {
                            if (!in_array($date->toDateString(), $timelogDates)) {
                                $absentCount++;
                            }
                        }
                    }
                }

                if (!$isFixedRate) {
                    foreach ($details->timelogs as $timelog) {
                        if ($timelog->time_in) {
                            $timelogDate = \Carbon\Carbon::parse($timelog->date);
                            $dayOfWeek = strtolower($timelogDate->format('l'));

                            $expectedStart = \Carbon\Carbon::parse($timelog->date . " " . $details->schedule[$dayOfWeek . '_start_time']);
                            $actualClockIn = \Carbon\Carbon::parse($timelog->time_in);

                            if ($actualClockIn->greaterThan($expectedStart)) {
                                $lateMinutes = $actualClockIn->diffInMinutes($expectedStart);
                                $totalLateMinutes += $lateMinutes;
                            }
                        }

                        if ($timelog->time_out) {
                            $timelogDate = \Carbon\Carbon::parse($timelog->date);
                            $dayOfWeek = strtolower($timelogDate->format('l'));

                            $expectedEnd = \Carbon\Carbon::parse($timelog->date . " " . $details->schedule[$dayOfWeek . '_end_time']);
                            $actualClockOut = \Carbon\Carbon::parse($timelog->time_out);

                            if ($actualClockOut->lessThan($expectedEnd)) {
                                $lateUnder = $actualClockOut->diffInMinutes($expectedEnd);
                                $totalUnderTime += $lateUnder;
                            }
                        }
                    }
                }

                foreach ($details->ot_request as $ot) {
                    if ($ot->start_time) {
                        $totalOTMinutes += Carbon::parse($ot->start_time)->diffInMinutes(Carbon::parse($ot->end_time));
                    }
                }

                foreach ($details->leave_request as $leave) {
                    $leaveCount += $leave->total_leave_hours;
                }

                $ot_multiplier = Earnings::where('code', 'OT')->first();

                $details->late_hours = round($totalLateMinutes, 2);
                $details->undertime = round($totalUnderTime, 2);
                $details->ot_hours = round($totalOTMinutes / 60, 2) >= 1 ? round($totalOTMinutes / 60, 2) : 0;
                $details->ot_multiplier = $ot_multiplier->multiplier;
                $details->ot_amount = (floatval($details->employee->compensations->hourly_salary) * $details->ot_hours) * $ot_multiplier->multiplier;
                $details->leave_count = $leaveCount;
                $details->sep = $sep;
                $details->absent_count = $absentCount - $leaveCount;

                $details->ca = CashAdvance::where('employee_id', $details->employee_id)->where('summary_id', $details->summary_id)->sum('amount');

                // === HOLIDAY LOGIC (UPDATED) ===
                $allHolidays = Holiday::with('holiday_type')
                    ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->get();

                $validHolidays = $allHolidays->filter(function ($holiday) use ($details) {
                    $dayOfWeek = strtolower(\Carbon\Carbon::parse($holiday->date)->format('l'));
                    return !empty($details->schedule[$dayOfWeek . '_start_time']);
                });

                if (!$isFixedRate) {
                    $details->holiday = $validHolidays->count();
                    $details->holiday_data = $validHolidays->values();
                }
                else {
                    $details->holiday = 0;
                    $details->holiday_data = null;
                }

                $details->allowance_days = AllowanceSetup::where('employee_id', $details->employee_id)->where('sequence_no', $details->summary_id)->sum('days');
                $details->allowance_amount = AllowanceSetup::where('employee_id', $details->employee_id)->where('sequence_no', $details->summary_id)->sum('amount');

                $daily_rate = $details->daily !== "0" ? floatval($details->daily) : (isset($details->employee->compensations) ? $details->employee->compensations->daily_salary : 0);
                $hourly_rate = $details->hourly !== "0" ? floatval($details->hourly) : (isset($details->employee->compensations) ? $details->employee->compensations->hourly_salary : 0);

                $monthly_rate = $details->monthly !== "0" ? floatval($details->monthly) : (isset($details->employee->compensations) ? $details->employee->compensations->monthly_salary : 0);


                
                if ($isFixedRate) {
                    $worked_days = 0;
                    for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                        $dayOfWeek = strtolower($date->format('l'));
                        if (!empty($details->schedule[$dayOfWeek . '_start_time'])) {
                            $worked_days++;
                        }
                    }

                    $details->for_fixed = $worked_days;
                    $absentCount = 0;
                } else {
                    $worked_days = intval(count($details->timelogs));
                    $details->for_fixed = null;
                }

                $leave_amount = floatval($details->leave_count * $daily_rate);

                $holiday_rate = 0;
                if($details->holiday_data !== null) {
                    foreach ($details->holiday_data as $h_item) {
                        $holiday_rate += $daily_rate * floatval($h_item->holiday_type->multiplier);
                    }
                }

                $late_rate = ($details->late_hours / 60) * $hourly_rate;
                $absent_rate = $absentCount * $daily_rate;
                $tardiness_deduct = $absent_rate === 0 ? $late_rate : ($absent_rate - $late_rate);

                $gross_salary = (($type === 2 ? ($monthly_rate / 2) : ($worked_days * $daily_rate)) +
                    $details->ot_amount + $details->allowance_amount + $leave_amount + $holiday_rate) - $tardiness_deduct;

                $details->tax_final = $this->getTax($type, $gross_salary);

                return $details;
            });

        return response()->json(compact('details'));
    }


}
