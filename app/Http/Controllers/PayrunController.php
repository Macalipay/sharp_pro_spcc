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
use App\ScheduleRequest;
use App\Classes\Computation\Payroll\WithholdingTax as WithholdingTax_Benefits;

class PayrunController extends Controller
{
    protected $withholding_tax;

    public function __construct() 
    {
        $this->withholding_tax = new WithholdingTax_Benefits();
    }

    private function getApprovedScheduleRequestForPeriod(int $employeeId, string $start, string $end)
    {
        return ScheduleRequest::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('period_start', [$start, $end])
                    ->orWhereBetween('period_end', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('period_start', '<=', $start)
                            ->where('period_end', '>=', $end);
                    });
            })
            ->orderBy('approved_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();
    }

    private function getExpectedScheduleWindow($details, Carbon $date, $approvedScheduleRequest): array
    {
        $dayOfWeek = strtolower($date->format('l'));
        $calendarStart = $details->schedule[$dayOfWeek . '_start_time'] ?? null;
        $calendarEnd = $details->schedule[$dayOfWeek . '_end_time'] ?? null;

        if ($approvedScheduleRequest) {
            $requestStartDate = Carbon::parse($approvedScheduleRequest->period_start)->startOfDay();
            $requestEndDate = Carbon::parse($approvedScheduleRequest->period_end)->endOfDay();
            if ($date->between($requestStartDate, $requestEndDate)) {
                return [
                    $approvedScheduleRequest->start_time ?: $calendarStart,
                    $approvedScheduleRequest->end_time ?: $calendarEnd,
                ];
            }
        }

        return [$calendarStart, $calendarEnd];
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
                        COALESCE(payroll_summaries.workflow_status, 0) AS workflow_status,
                        SUM(CASE WHEN payroll_summary_details.status = 1 THEN 1 ELSE 0 END) no_of_employee,
                        SUM(CASE WHEN payroll_summary_details.status = 0 THEN 1 ELSE 0 END) pending_employee,
                        SUM(CASE WHEN payroll_summary_details.deleted_at IS NULL THEN 1 ELSE 0 END) AS total_of_employee,
                        SUM(CASE WHEN payroll_summary_details.status = 1 THEN payroll_summary_details.gross_earnings ELSE 0 END) amount,
                        SUM(CASE WHEN payroll_summary_details.status = 1 THEN payroll_summary_details.net_pay ELSE 0 END) net_amount,
                        payroll_summaries.status')
                        ->leftJoin('payroll_summary_details', 'payroll_summary_details.summary_id', '=', 'payroll_summaries.id')
                        ->join('payroll_calendars', 'payroll_summaries.sequence_title', '=', 'payroll_calendars.id');
    
            if ($request->status !== null && $request->status !== '') {
                $summary->whereRaw('COALESCE(payroll_summaries.workflow_status, 0) = ?', [(int)$request->status]);
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

            if ($request->keyword !== null && trim($request->keyword) !== '') {
                $keyword = trim($request->keyword);
                $summary->where(function ($q) use ($keyword) {
                    $q->where('payroll_summaries.sequence_no', 'like', "%{$keyword}%")
                      ->orWhere('payroll_calendars.title', 'like', "%{$keyword}%")
                      ->orWhere('payroll_summaries.period_start', 'like', "%{$keyword}%")
                      ->orWhere('payroll_summaries.payroll_period', 'like', "%{$keyword}%");
                });
            }

            $periodOrder = strtolower((string)$request->period_order) === 'asc' ? 'asc' : 'desc';
            $summary->orderByRaw("STR_TO_DATE(payroll_summaries.payroll_period, '%Y-%m-%d') {$periodOrder}")
                    ->orderBy('payroll_summaries.id', 'desc');
    
            $summary = $summary->groupBy('payroll_summaries.id', 
                        'payroll_calendars.title', 
                        'payroll_summaries.sequence_no', 
                        'payroll_summaries.schedule_type', 
                        'payroll_summaries.period_start', 
                        'payroll_summaries.payroll_period', 
                        'payroll_summaries.workflow_status',
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
            $approvedScheduleRequest = $this->getApprovedScheduleRequestForPeriod(
                intval($details->employee_id),
                $startDate->toDateString(),
                $endDate->toDateString()
            );
            $details->applied_schedule_request = $approvedScheduleRequest;
            $isFlexiTime = !$approvedScheduleRequest && intval($details->schedule->is_flexi_time ?? 0) === 1;

            if (!$isFixedRate) {
                for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                    list($expectedStartTime, $expectedEndTime) = $this->getExpectedScheduleWindow($details, $date, $approvedScheduleRequest);
                    if (!empty($expectedStartTime) && !empty($expectedEndTime)) {
                        if (!in_array($date->toDateString(), $timelogDates)) {
                            $absentCount++;
                        }
                    }
                }
            }

            if (!$isFixedRate) {
                foreach ($details->timelogs as $timelog) {
                    $timelogDate = \Carbon\Carbon::parse($timelog->date);
                    list($expectedStartTime, $expectedEndTime) = $this->getExpectedScheduleWindow($details, $timelogDate, $approvedScheduleRequest);

                    if ($timelog->time_in) {
                        if (!empty($expectedStartTime) && !$isFlexiTime) {
                            $expectedStart = \Carbon\Carbon::parse($timelog->date . " " . $expectedStartTime);
                            $actualClockIn = \Carbon\Carbon::parse($timelog->time_in);

                            if ($actualClockIn->greaterThan($expectedStart)) {
                                $lateMinutes = $actualClockIn->diffInMinutes($expectedStart);
                                $totalLateMinutes += $lateMinutes;
                            }
                        }
                    }

                    if ($timelog->time_out) {
                        if ($isFlexiTime && $timelog->time_in) {
                            $actualClockIn = \Carbon\Carbon::parse($timelog->time_in);
                            $actualClockOut = \Carbon\Carbon::parse($timelog->time_out);
                            $workedMinutes = $actualClockIn->diffInMinutes($actualClockOut);
                            if ($workedMinutes < 540) {
                                $totalUnderTime += (540 - $workedMinutes);
                            }
                        } elseif (!empty($expectedEndTime)) {
                            $expectedEnd = \Carbon\Carbon::parse($timelog->date . " " . $expectedEndTime);
                            $actualClockOut = \Carbon\Carbon::parse($timelog->time_out);

                            if ($actualClockOut->lessThan($expectedEnd)) {
                                $lateUnder = $actualClockOut->diffInMinutes($expectedEnd);
                                $totalUnderTime += $lateUnder;
                            }
                        }
                    } elseif ($isFlexiTime && $timelog->time_in) {
                        $totalUnderTime += 540;
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
            $scheduleType = $details->header ? intval($details->header->schedule_type) : intval($type);
            $recomputedContributions = $this->computeEmployeeContributionsBySchedule($monthly_rate, $scheduleType);
            $details->sss = $recomputedContributions['sss'];
            $details->pagibig = $recomputedContributions['pagibig'];
            $details->philhealth = $recomputedContributions['philhealth'];

            if ($isFixedRate) {
                $worked_days = 0;
                for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                    list($expectedStartTime, $expectedEndTime) = $this->getExpectedScheduleWindow($details, $date, $approvedScheduleRequest);
                    if (!empty($expectedStartTime) && !empty($expectedEndTime)) {
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
            $appliedTax = floatval($details->tax) !== 0.0 ? floatval($details->tax) : floatval($details->tax_final);
            $computedNetPay = $gross_salary - (
                floatval($details->sss) +
                floatval($details->pagibig) +
                floatval($details->philhealth) +
                $appliedTax +
                floatval($details->ca)
            );

            // Keep payroll details totals in sync so Payroll Summary reads the same values.
            PayrollSummaryDetails::where('id', $details->id)->update([
                'gross_earnings' => $gross_salary,
                'sss' => $details->sss,
                'pagibig' => $details->pagibig,
                'philhealth' => $details->philhealth,
                'net_pay' => $computedNetPay,
                'tax' => $appliedTax,
                'updated_by' => Auth::user()->id,
            ]);
            $details->gross_earnings = $gross_salary;
            $details->net_pay = $computedNetPay;
            $details->tax = $appliedTax;

            return $details;
        });

        $summary = PayrollSummary::select('id', 'workflow_status')->where('id', $request->id)->first();
        $approvedEmployeeCount = PayrollSummaryDetails::where('summary_id', $request->id)->where('status', 1)->count();
        $totalEmployeeCount = PayrollSummaryDetails::where('summary_id', $request->id)->count();
        $allEmployeeApproved = ($totalEmployeeCount > 0 && $approvedEmployeeCount === $totalEmployeeCount);

        return response()->json(compact('details', 'summary', 'approvedEmployeeCount', 'totalEmployeeCount', 'allEmployeeApproved'));
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
            "workflow_status" => 0,
            "submitted_by" => null,
            "submitted_at" => null,
            "approved_by" => null,
            "approved_at" => null,
            "payment_submitted_by" => null,
            "payment_submitted_at" => null,
            "workstation_id" => Auth::user()->workstation_id,
            "created_by" => Auth::user()->id,
            "updated_by" => Auth::user()->id,
        );

        $summary = PayrollSummary::where('period_start', $request->period_start)
            ->where('payroll_period', $request->payroll_period)
            ->count();

        if($summary === 0) {
            $record = PayrollSummary::create($data);
    
            $employments = Employment::join('payroll_calendars', 'payroll_calendars.id', '=', 'employments.payroll_calendar_id')
                ->join('employees', 'employees.id', '=', 'employments.employee_id')
                ->join('compensations', 'employees.id', '=', 'compensations.employee_id')
                ->where('payroll_calendar_id', $request->sequence_title)
                ->get();
            
            
            foreach($employments as $item) {
                $contributions = $this->computeEmployeeContributionsBySchedule(
                    floatval($item->monthly_salary ?? 0),
                    intval($request->payment_schedule)
                );

                $details = array(
                    "employee_id" => $item->employee_id,
                    "sequence_no" => $code."-".date('mdY', strtotime($request->payroll_period)),
                    "summary_id" => $record->id,
                    "gross_earnings" => 0,
                    "sss" => $contributions['sss'],
                    "pagibig" => $contributions['pagibig'],
                    "philhealth" => $contributions['philhealth'],
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
            return response()->json(['responseJSON' => ["message" => "Payroll with the same period coverage already exists."]], 500);
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
            "workflow_status" => 0,
            "submitted_by" => null,
            "submitted_at" => null,
            "approved_by" => null,
            "approved_at" => null,
            "payment_submitted_by" => null,
            "payment_submitted_at" => null,
            "workstation_id" => Auth::user()->workstation_id,
            "created_by" => Auth::user()->id,
            "updated_by" => Auth::user()->id,
        );

        $duplicateCoverage = PayrollSummary::where('period_start', $request->period_start)
            ->where('payroll_period', $request->payroll_period)
            ->where('id', '!=', $id)
            ->count();

        if ($duplicateCoverage !== 0) {
            return response()->json(['responseJSON' => ["message" => "Payroll with the same period coverage already exists."]], 500);
        }

        $record = PayrollSummary::where('id', $id)->update($data);
        // PayrollSummaryDetails::where('summary_id', $id)->delete();

        $employments = Employment::join('payroll_calendars', 'payroll_calendars.id', '=', 'employments.payroll_calendar_id')
            ->join('employees', 'employees.id', '=', 'employments.employee_id')
            ->join('compensations', 'employees.id', '=', 'compensations.employee_id')
            ->where('payroll_calendar_id', $request->sequence_title)
            ->get();
            
        
        foreach($employments as $item) {
            $contributions = $this->computeEmployeeContributionsBySchedule(
                floatval($item->monthly_salary ?? 0),
                intval($request->payment_schedule)
            );

            $details = array(
                "employee_id" => $item->employee_id,
                "sequence_no" => $code."-".date('mdY', strtotime($request->payroll_period)),
                "summary_id" => $id,
                "gross_earnings" => 0,
                "sss" => $contributions['sss'],
                "pagibig" => $contributions['pagibig'],
                "philhealth" => $contributions['philhealth'],
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

    public function submitForApproval(Request $request)
    {
        $summary = PayrollSummary::where('id', $request->id)->firstOrFail();

        $summary->workflow_status = 1; // Submitted for approval
        $summary->submitted_by = Auth::user()->id;
        $summary->submitted_at = Carbon::now();
        $summary->approved_by = null;
        $summary->approved_at = null;
        $summary->payment_submitted_by = null;
        $summary->payment_submitted_at = null;
        $summary->updated_by = Auth::user()->id;
        $summary->save();

        return response()->json(['message' => 'Submitted for approval.']);
    }

    public function approveSummary(Request $request)
    {
        $summary = PayrollSummary::where('id', $request->id)->firstOrFail();

        if ((int)($summary->workflow_status ?? 0) !== 1) {
            return response()->json(['message' => 'Only submitted payroll can be approved.'], 422);
        }

        $summary->workflow_status = 2; // Approved
        $summary->approved_by = Auth::user()->id;
        $summary->approved_at = Carbon::now();
        $summary->updated_by = Auth::user()->id;
        $summary->save();

        return response()->json(['message' => 'Payroll approved.']);
    }

    public function revertSummary(Request $request)
    {
        $summary = PayrollSummary::where('id', $request->id)->firstOrFail();

        $summary->workflow_status = 0; // Preparing
        $summary->submitted_by = null;
        $summary->submitted_at = null;
        $summary->approved_by = null;
        $summary->approved_at = null;
        $summary->payment_submitted_by = null;
        $summary->payment_submitted_at = null;
        $summary->updated_by = Auth::user()->id;
        $summary->save();

        return response()->json(['message' => 'Payroll reverted to preparing.']);
    }

    public function submitForPayment(Request $request)
    {
        $summary = PayrollSummary::where('id', $request->id)->firstOrFail();

        if ((int)($summary->workflow_status ?? 0) !== 2) {
            return response()->json(['message' => 'Payroll must be approved before submitting for payment.'], 422);
        }

        $approvedEmployeeCount = PayrollSummaryDetails::where('summary_id', $summary->id)->where('status', 1)->count();
        $totalEmployeeCount = PayrollSummaryDetails::where('summary_id', $summary->id)->count();
        $allEmployeeApproved = ($totalEmployeeCount > 0 && $approvedEmployeeCount === $totalEmployeeCount);

        if (!$allEmployeeApproved) {
            return response()->json(['message' => 'All employee payroll entries must be approved before payment submission.'], 422);
        }

        $summary->workflow_status = 3; // Submitted for payment
        $summary->payment_submitted_by = Auth::user()->id;
        $summary->payment_submitted_at = Carbon::now();
        $summary->updated_by = Auth::user()->id;
        $summary->save();

        return response()->json(['message' => 'Payroll submitted for payment.']);
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

    private function computeEmployeeContributionsBySchedule(float $monthlySalary, int $scheduleType): array
    {
        // Employee share (monthly basis)
        $sssBasis = min($monthlySalary, 35000);
        $sss = $sssBasis * 0.05;

        if ($monthlySalary <= 10000) {
            $philhealth = 250;
        } elseif ($monthlySalary <= 100000) {
            $philhealth = ($monthlySalary * 0.05) / 2;
        } else {
            $philhealth = 2500;
        }

        if ($monthlySalary <= 10000) {
            $pagibig = $monthlySalary * 0.02;
        } else {
            $pagibig = 200;
        }

        // Period conversion
        $divisor = 1;
        if (in_array($scheduleType, [2, 3], true)) {
            $divisor = 2; // semi-monthly / semi-weekly
        } elseif ($scheduleType === 4) {
            $divisor = 4; // weekly
        }

        return [
            'sss' => round($sss / $divisor, 2),
            'pagibig' => round($pagibig / $divisor, 2),
            'philhealth' => round($philhealth / $divisor, 2),
        ];
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
                $approvedScheduleRequest = $this->getApprovedScheduleRequestForPeriod(
                    intval($details->employee_id),
                    $startDate->toDateString(),
                    $endDate->toDateString()
                );
                $details->applied_schedule_request = $approvedScheduleRequest;
                $isFlexiTime = !$approvedScheduleRequest && intval($details->schedule->is_flexi_time ?? 0) === 1;

                if (!$isFixedRate) {
                    for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                        list($expectedStartTime, $expectedEndTime) = $this->getExpectedScheduleWindow($details, $date, $approvedScheduleRequest);
                        if (!empty($expectedStartTime) && !empty($expectedEndTime)) {
                            if (!in_array($date->toDateString(), $timelogDates)) {
                                $absentCount++;
                            }
                        }
                    }
                }

                if (!$isFixedRate) {
                    foreach ($details->timelogs as $timelog) {
                        $timelogDate = \Carbon\Carbon::parse($timelog->date);
                        list($expectedStartTime, $expectedEndTime) = $this->getExpectedScheduleWindow($details, $timelogDate, $approvedScheduleRequest);

                        if ($timelog->time_in) {
                            if (!empty($expectedStartTime) && !$isFlexiTime) {
                                $expectedStart = \Carbon\Carbon::parse($timelog->date . " " . $expectedStartTime);
                                $actualClockIn = \Carbon\Carbon::parse($timelog->time_in);

                                if ($actualClockIn->greaterThan($expectedStart)) {
                                    $lateMinutes = $actualClockIn->diffInMinutes($expectedStart);
                                    $totalLateMinutes += $lateMinutes;
                                }
                            }
                        }

                        if ($timelog->time_out) {
                            if ($isFlexiTime && $timelog->time_in) {
                                $actualClockIn = \Carbon\Carbon::parse($timelog->time_in);
                                $actualClockOut = \Carbon\Carbon::parse($timelog->time_out);
                                $workedMinutes = $actualClockIn->diffInMinutes($actualClockOut);
                                if ($workedMinutes < 540) {
                                    $totalUnderTime += (540 - $workedMinutes);
                                }
                            } elseif (!empty($expectedEndTime)) {
                                $expectedEnd = \Carbon\Carbon::parse($timelog->date . " " . $expectedEndTime);
                                $actualClockOut = \Carbon\Carbon::parse($timelog->time_out);

                                if ($actualClockOut->lessThan($expectedEnd)) {
                                    $lateUnder = $actualClockOut->diffInMinutes($expectedEnd);
                                    $totalUnderTime += $lateUnder;
                                }
                            }
                        } elseif ($isFlexiTime && $timelog->time_in) {
                            $totalUnderTime += 540;
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
                $scheduleType = $details->header ? intval($details->header->schedule_type) : intval($type);
                $recomputedContributions = $this->computeEmployeeContributionsBySchedule($monthly_rate, $scheduleType);
                $details->sss = $recomputedContributions['sss'];
                $details->pagibig = $recomputedContributions['pagibig'];
                $details->philhealth = $recomputedContributions['philhealth'];


                
                if ($isFixedRate) {
                    $worked_days = 0;
                    for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                        list($expectedStartTime, $expectedEndTime) = $this->getExpectedScheduleWindow($details, $date, $approvedScheduleRequest);
                        if (!empty($expectedStartTime) && !empty($expectedEndTime)) {
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
                $appliedTax = floatval($details->tax) !== 0.0 ? floatval($details->tax) : floatval($details->tax_final);
                $computedNetPay = $gross_salary - (
                    floatval($details->sss) +
                    floatval($details->pagibig) +
                    floatval($details->philhealth) +
                    $appliedTax +
                    floatval($details->ca)
                );

                PayrollSummaryDetails::where('id', $details->id)->update([
                    'gross_earnings' => $gross_salary,
                    'sss' => $details->sss,
                    'pagibig' => $details->pagibig,
                    'philhealth' => $details->philhealth,
                    'net_pay' => $computedNetPay,
                    'tax' => $appliedTax,
                    'updated_by' => Auth::user()->id,
                ]);
                $details->gross_earnings = $gross_salary;
                $details->net_pay = $computedNetPay;
                $details->tax = $appliedTax;

                return $details;
            });

        return response()->json(compact('details'));
    }


}
