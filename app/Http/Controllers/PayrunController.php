<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Carbon\Carbon;
use App\AllowanceTagging;
use App\AllowanceSetup;
use App\CashAdvance;
use App\Holiday;
use App\Employment;
use App\PayrollSummary;
use App\PayrollSummaryDetails;
use App\PayrollSummaryNote;
use App\TimeLogApprovals;
use App\AccountingBill;
use App\AccountingBillItem;
use App\EmployeeInformation;
use App\EmployeeDeduction;
use App\EmployeeDeductionTransaction;
use App\PayrollCalendar;
use App\OvertimeRequest;
use App\Earnings;
use App\TimeLogs;
use App\Allowance;
use App\LeaveRequest;
use App\ScheduleRequest;
use App\Classes\Computation\Payroll\WithholdingTax as WithholdingTax_Benefits;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

    private function getApprovedTimesheetEmployeesForPeriod(int $calendarId, string $periodStart, string $periodEnd)
    {
        return Employment::query()
            ->join('employees', 'employees.id', '=', 'employments.employee_id')
            ->join('compensations', 'employees.id', '=', 'compensations.employee_id')
            ->join('time_log_approvals', 'time_log_approvals.employee_id', '=', 'employments.employee_id')
            ->where('employments.payroll_calendar_id', $calendarId)
            ->where('time_log_approvals.status', 1)
            ->where('time_log_approvals.start_date', '<=', $periodEnd)
            ->where('time_log_approvals.end_date', '>=', $periodStart)
            ->select(
                'employments.employee_id',
                'compensations.daily_salary',
                'compensations.monthly_salary',
                'compensations.hourly_salary'
            )
            ->distinct()
            ->get();
    }

    private function getPayrollBasisLabel(?string $employmentType): string
    {
        switch ($employmentType) {
            case 'fixed_rate':
                return 'Fixed Rate';
            case 'monthly_rate':
                return 'Monthly';
            case 'daily_rate':
                return 'Daily';
            default:
                return 'Not Set';
        }
    }

    private function computeReflectedAllowance(float $encodedAmount, ?string $employmentType, float $presentDays): array
    {
        $encodedAmount = round($encodedAmount, 2);
        $presentDays = round($presentDays, 2);

        if ($encodedAmount <= 0) {
            return [
                'reflected_amount' => 0,
                'formula' => 'No allowance amount encoded',
                'warning' => null,
            ];
        }

        switch ($employmentType) {
            case 'fixed_rate':
                return [
                    'reflected_amount' => round($encodedAmount / 2, 2),
                    'formula' => sprintf('%s / 2', number_format($encodedAmount, 2, '.', ',')),
                    'warning' => null,
                ];

            case 'monthly_rate':
                return [
                    'reflected_amount' => round(($encodedAmount / 26) * $presentDays, 2),
                    'formula' => sprintf('(%s / 26) x %s', number_format($encodedAmount, 2, '.', ','), rtrim(rtrim(number_format($presentDays, 2, '.', ''), '0'), '.')),
                    'warning' => null,
                ];

            case 'daily_rate':
                return [
                    'reflected_amount' => round($encodedAmount * $presentDays, 2),
                    'formula' => sprintf('%s x %s', number_format($encodedAmount, 2, '.', ','), rtrim(rtrim(number_format($presentDays, 2, '.', ''), '0'), '.')),
                    'warning' => null,
                ];

            default:
                return [
                    'reflected_amount' => 0,
                    'formula' => 'Payroll group is not set',
                    'warning' => 'Employee payroll group is missing. Reflected allowance was not computed.',
                ];
        }
    }

    private function getComputedAllowanceData($details, float $presentDays): array
    {
        $employmentType = optional($details->employee)->employment_type;
        $payrollBasis = $this->getPayrollBasisLabel($employmentType);

        $employeeAllowances = AllowanceTagging::with('allowances')
            ->where('employee_id', $details->employee_id);

        if (Schema::hasColumn('allowance_taggings', 'auto_reflect_in_payroll')) {
            $employeeAllowances->where('auto_reflect_in_payroll', true);
        }

        $employeeAllowances = $employeeAllowances
            ->orderBy('id', 'asc')
            ->get();

        $manualAllowances = AllowanceSetup::with('allowances')
            ->where('employee_id', $details->employee_id)
            ->where('sequence_no', $details->summary_id)
            ->orderBy('id', 'asc')
            ->get();

        $breakdown = [];
        $reflectedTotal = 0;
        $manualTotal = 0;

        foreach ($employeeAllowances as $item) {
            $encodedAmount = round(floatval($item->amount ?? optional($item->allowances)->amount ?? 0), 2);
            $computed = $this->computeReflectedAllowance($encodedAmount, $employmentType, $presentDays);
            $reflectedTotal += $computed['reflected_amount'];

            $breakdown[] = [
                'source' => 'employee_compensation',
                'type' => optional($item->allowances)->name ?? 'Allowance',
                'encoded_amount' => $encodedAmount,
                'payroll_basis' => $payrollBasis,
                'payroll_basis_code' => $employmentType,
                'present_days' => in_array($employmentType, ['monthly_rate', 'daily_rate'], true) ? $presentDays : null,
                'reflected_amount' => $computed['reflected_amount'],
                'formula' => $computed['formula'],
                'warning' => $computed['warning'],
            ];
        }

        foreach ($manualAllowances as $item) {
            $manualAmount = round(floatval($item->amount), 2);
            $manualTotal += $manualAmount;

            $breakdown[] = [
                'source' => 'manual_payroll',
                'type' => (optional($item->allowances)->name ?? 'Allowance') . ' (Manual)',
                'encoded_amount' => $manualAmount,
                'payroll_basis' => 'Manual Payroll Entry',
                'payroll_basis_code' => 'manual',
                'present_days' => $item->days !== null ? floatval($item->days) : null,
                'reflected_amount' => $manualAmount,
                'formula' => sprintf('Manual payroll allowance entry%s', $item->days !== null ? ' x ' . rtrim(rtrim(number_format(floatval($item->days), 2, '.', ''), '0'), '.') . ' day(s)' : ''),
                'warning' => null,
            ];
        }

        return [
            'present_days' => $presentDays,
            'payroll_basis' => $payrollBasis,
            'payroll_basis_code' => $employmentType,
            'employee_allowance_amount' => round($reflectedTotal, 2),
            'manual_allowance_amount' => round($manualTotal, 2),
            'total_allowance_amount' => round($reflectedTotal + $manualTotal, 2),
            'breakdown' => $breakdown,
            'warning' => $employmentType ? null : 'Employee payroll group is missing. Reflected allowance from compensation history was not computed.',
        ];
    }

    private function getDeductionFrequencyLabel(?string $frequency): string
    {
        switch ($frequency) {
            case 'semi_monthly':
                return 'Semi-Monthly Payroll Only';
            case 'weekly':
                return 'Weekly Payroll Only';
            case 'monthly':
                return 'Monthly Payroll Only';
            case 'one_time':
                return 'One-Time';
            case 'every_payroll':
            default:
                return 'Every Payroll';
        }
    }

    private function shouldApplyEmployeeDeduction(EmployeeDeduction $deduction, $summary, string $periodStart, string $periodEnd): bool
    {
        if (!$deduction->auto_deduct_in_payroll) {
            return false;
        }

        if ($deduction->status !== 'active') {
            return false;
        }

        if (round(floatval($deduction->remaining_balance), 2) <= 0) {
            return false;
        }

        if ($deduction->effective_start_payroll && Carbon::parse($summary->payroll_period ?? $periodEnd)->lt(Carbon::parse($deduction->effective_start_payroll)->startOfDay())) {
            return false;
        }

        if ($deduction->end_date && Carbon::parse($summary->period_start ?? $periodStart)->gt(Carbon::parse($deduction->end_date)->endOfDay())) {
            return false;
        }

        $scheduleType = intval($summary->schedule_type ?? 0);

        switch ($deduction->deduction_frequency) {
            case 'semi_monthly':
                return in_array($scheduleType, [2, 3], true);
            case 'weekly':
                return in_array($scheduleType, [1, 4], true);
            case 'monthly':
                return $scheduleType === 3;
            case 'one_time':
                return true;
            case 'every_payroll':
            default:
                return true;
        }
    }

    private function computeScheduledEmployeeDeductionAmount(EmployeeDeduction $deduction): float
    {
        $remaining = max(0, round(floatval($deduction->remaining_balance), 2));
        $perPayroll = round(floatval($deduction->deduction_per_payroll), 2);

        if ($remaining <= 0) {
            return 0;
        }

        if ($perPayroll <= 0 && intval($deduction->payment_terms) > 0) {
            $perPayroll = round(floatval($deduction->total_amount) / max(intval($deduction->payment_terms), 1), 2);
        }

        if ($perPayroll <= 0) {
            $perPayroll = $remaining;
        }

        return round(min($remaining, $perPayroll), 2);
    }

    private function getComputedEmployeeDeductionData($details, string $periodStart, string $periodEnd): array
    {
        $summary = $details->header;
        if (!$summary) {
            return [
                'total_amount' => 0,
                'breakdown' => [],
            ];
        }

        $postedTransactions = EmployeeDeductionTransaction::with('deduction')
            ->where('summary_id', $details->summary_id)
            ->where('employee_id', $details->employee_id)
            ->where('source', 'auto_payroll')
            ->orderBy('id', 'asc')
            ->get();

        if ($postedTransactions->count() > 0) {
            $breakdown = $postedTransactions->map(function ($item) {
                return [
                    'employee_deduction_id' => $item->employee_deduction_id,
                    'deduction_type' => optional($item->deduction)->name ?: 'Deduction',
                    'reference_name' => $item->reference_name ?: '-',
                    'scheduled_amount' => round(floatval($item->scheduled_amount), 2),
                    'actual_deducted_amount' => round(floatval($item->actual_deducted_amount), 2),
                    'running_balance' => round(floatval($item->running_balance), 2),
                    'frequency' => 'auto_payroll',
                    'formula' => 'Auto payroll deduction',
                    'status' => $item->status ?: 'posted',
                ];
            })->values()->all();

            return [
                'total_amount' => round($postedTransactions->sum(function ($item) {
                    return floatval($item->actual_deducted_amount);
                }), 2),
                'breakdown' => $breakdown,
            ];
        }

        $deductions = EmployeeDeduction::with('deduction')
            ->where('employee_id', $details->employee_id)
            ->orderBy('id', 'asc')
            ->get();

        $breakdown = [];
        $total = 0;

        foreach ($deductions as $deduction) {
            if (!$this->shouldApplyEmployeeDeduction($deduction, $summary, $periodStart, $periodEnd)) {
                continue;
            }

            $scheduled = $this->computeScheduledEmployeeDeductionAmount($deduction);
            if ($scheduled <= 0) {
                continue;
            }

            $total += $scheduled;
            $breakdown[] = [
                'employee_deduction_id' => $deduction->id,
                'deduction_type' => optional($deduction->deduction)->name ?: 'Deduction',
                'reference_name' => $deduction->reference_name ?: '-',
                'scheduled_amount' => $scheduled,
                'actual_deducted_amount' => $scheduled,
                'running_balance' => round(max(0, floatval($deduction->remaining_balance) - $scheduled), 2),
                'frequency' => $deduction->deduction_frequency,
                'formula' => sprintf(
                    '%s%s',
                    $scheduled < round(floatval($deduction->deduction_per_payroll), 2) && round(floatval($deduction->remaining_balance), 2) < round(floatval($deduction->deduction_per_payroll), 2)
                        ? 'Remaining balance only'
                        : 'Scheduled per payroll deduction',
                    $deduction->payment_terms ? ' (' . intval($deduction->payment_terms) . ' terms)' : ''
                ),
                'status' => $deduction->status,
            ];
        }

        return [
            'total_amount' => round($total, 2),
            'breakdown' => $breakdown,
        ];
    }

    private function syncApprovedTimesheetPayrollDetails(PayrollSummary $summary, int $calendarId, string $periodStart, string $periodEnd, int $scheduleType, bool $removeMissing = true): void
    {
        $approvedEmployees = $this->getApprovedTimesheetEmployeesForPeriod($calendarId, $periodStart, $periodEnd);
        $approvedEmployeeIds = $approvedEmployees->pluck('employee_id')->map(function ($id) {
            return (int) $id;
        })->all();

        if ($removeMissing) {
            $detailsToDelete = PayrollSummaryDetails::query()
                ->where('summary_id', $summary->id);

            if (!empty($approvedEmployeeIds)) {
                $detailsToDelete->whereNotIn('employee_id', $approvedEmployeeIds);
            }

            $detailsToDelete->delete();
        }

        foreach ($approvedEmployees as $item) {
            $contributions = $this->computeEmployeeContributionsBySchedule(
                floatval($item->monthly_salary ?? 0),
                $scheduleType
            );

            $details = [
                "employee_id" => $item->employee_id,
                "sequence_no" => $summary->sequence_no,
                "summary_id" => $summary->id,
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
            ];

            $existing = PayrollSummaryDetails::withTrashed()
                ->where('summary_id', $summary->id)
                ->where('employee_id', $item->employee_id)
                ->first();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }

                $existing->update($details);
                continue;
            }

            PayrollSummaryDetails::create($details);
        }
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

            $presentDays = intval(count($details->timelogs));
            $computedAllowance = $this->getComputedAllowanceData($details, $presentDays);
            $details->allowance_days = $computedAllowance['present_days'];
            $details->allowance_payroll_basis = $computedAllowance['payroll_basis'];
            $details->allowance_payroll_basis_code = $computedAllowance['payroll_basis_code'];
            $details->allowance_employee_amount = $computedAllowance['employee_allowance_amount'];
            $details->allowance_manual_amount = $computedAllowance['manual_allowance_amount'];
            $details->allowance_amount = $computedAllowance['total_allowance_amount'];
            $details->allowance_breakdown = $computedAllowance['breakdown'];
            $details->allowance_warning = $computedAllowance['warning'];

            $computedEmployeeDeductions = $this->getComputedEmployeeDeductionData($details, $request->start, $request->end);
            $details->employee_deduction_amount = $computedEmployeeDeductions['total_amount'];
            $details->employee_deduction_breakdown = $computedEmployeeDeductions['breakdown'];

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
                floatval($details->ca) +
                floatval($details->employee_deduction_amount)
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

            $this->syncApprovedTimesheetPayrollDetails(
                $record,
                intval($request->sequence_title),
                $request->period_start,
                $request->payroll_period,
                intval($request->payment_schedule)
            );
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
            $timeIn = $this->normalizePayrunDetailDateTime($item['date'] ?? null, $item['time_in'] ?? null, false);
            $timeOut = $this->normalizePayrunDetailDateTime($item['date'] ?? null, $item['time_out'] ?? null, !empty($item['is_next_day_timeout']));
            $breakHours = ($timeIn !== null && $timeOut !== null) ? 1 : 0;
            $totalHours = $this->calculatePayrunRegularHours($timeIn, $timeOut, $breakHours);

            if($item['id'] !== "null") {
                TimeLogs::where('id', $item['id'])->update([
                    "time_in" => $timeIn,
                    "time_out" => $timeOut,
                    "break_in" => null,
                    "break_out" => null,
                    "break_hours" => $breakHours,
                    "total_hours" => $totalHours,
                ]);
            }
            else {
                if($timeIn !== null) {
                    $data = array(
                        "employee_id" => $request->emp_id,
                        "date" => $item['date'],
                        "time_in" => $timeIn,
                        "time_out" => $timeOut,
                        "break_in" => null,
                        "break_out" => null,
                        "total_hours" => $totalHours,
                        "break_hours" => $breakHours,
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

    private function normalizePayrunDetailDateTime($workDate, $value, $isNextDay)
    {
        if (empty($workDate) || empty($value)) {
            return null;
        }

        $valueString = trim((string) $value);

        if (preg_match('/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}(:\d{2})?$/', $valueString)) {
            $dateTime = Carbon::parse($valueString);
            if ($isNextDay && $dateTime->toDateString() === $workDate) {
                $dateTime->addDay();
            }

            return $dateTime->format('Y-m-d H:i:s');
        }

        if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $valueString)) {
            $dateTime = Carbon::parse($workDate . ' ' . (strlen($valueString) === 5 ? $valueString . ':00' : $valueString));
            if ($isNextDay) {
                $dateTime->addDay();
            }

            return $dateTime->format('Y-m-d H:i:s');
        }

        return $valueString;
    }

    private function calculatePayrunRegularHours($timeIn, $timeOut, $breakHours)
    {
        if ($timeIn === null || $timeOut === null) {
            return 0;
        }

        $seconds = strtotime($timeOut) - strtotime($timeIn);
        if ($seconds <= 0) {
            return 0;
        }

        return number_format(max(($seconds / 3600) - (float) $breakHours, 0), 2, '.', '');
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

        PayrollSummary::where('id', $id)->update($data);

        $summaryRecord = PayrollSummary::findOrFail($id);
        $this->syncApprovedTimesheetPayrollDetails(
            $summaryRecord,
            intval($request->sequence_title),
            $request->period_start,
            $request->payroll_period,
            intval($request->payment_schedule)
        );
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

        $workflowStatus = (int) ($summary->workflow_status ?? 0);
        if (!in_array($workflowStatus, [0, 4], true)) {
            return response()->json(['message' => 'Payroll must be submitted for audit first.'], 422);
        }

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

    public function submitForAudit(Request $request)
    {
        $summary = PayrollSummary::where('id', $request->id)->firstOrFail();

        if ((int)($summary->workflow_status ?? 0) !== 0) {
            return response()->json(['message' => 'Only draft payroll can be submitted for audit.'], 422);
        }

        $summary->workflow_status = 4; // Submitted for audit
        $summary->updated_by = Auth::user()->id;
        $summary->save();

        return response()->json(['message' => 'Submitted for audit.']);
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
        if ((int)($summary->workflow_status ?? 0) !== 4) {
            return response()->json(['message' => 'Only payroll submitted for audit can be reverted.'], 422);
        }

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

        $workflowStatus = (int) ($summary->workflow_status ?? 0);
        $legacyStatus = (int) ($summary->status ?? 0);

        if ($workflowStatus === 3) {
            $this->syncPayrollToDraftExpense($summary);
            return response()->json(['message' => 'Payroll already submitted for payment.']);
        }

        $approvedEmployeeCount = PayrollSummaryDetails::where('summary_id', $summary->id)->where('status', 1)->count();
        $totalEmployeeCount = PayrollSummaryDetails::where('summary_id', $summary->id)->count();
        $allEmployeeApproved = ($totalEmployeeCount > 0 && $approvedEmployeeCount === $totalEmployeeCount);

        $isApproved = ($workflowStatus === 2) || in_array($legacyStatus, [1, 2], true);
        if (!$isApproved && $allEmployeeApproved) {
            $summary->workflow_status = 2; // Auto-align to approved for legacy records.
            $summary->approved_by = Auth::user()->id;
            $summary->approved_at = Carbon::now();
            $summary->updated_by = Auth::user()->id;
            $summary->save();
            $isApproved = true;
        }

        if (!$isApproved) {
            return response()->json(['message' => 'Payroll must be approved before submitting for payment.'], 422);
        }

        if (!$allEmployeeApproved) {
            return response()->json(['message' => 'All employee payroll entries must be approved before payment submission.'], 422);
        }

        $summary->workflow_status = 3; // Submitted for payment
        $summary->payment_submitted_by = Auth::user()->id;
        $summary->payment_submitted_at = Carbon::now();
        $summary->updated_by = Auth::user()->id;
        $summary->save();

        $this->syncEmployeeDeductionTransactions($summary);
        $this->syncPayrollToDraftExpense($summary);

        return response()->json(['message' => 'Payroll submitted for payment.']);
    }

    private function syncEmployeeDeductionTransactions(PayrollSummary $summary): void
    {
        $detailsRows = PayrollSummaryDetails::with('header')
            ->where('summary_id', $summary->id)
            ->get();

        foreach ($detailsRows as $details) {
            $computed = $this->getComputedEmployeeDeductionData(
                $details,
                $summary->period_start,
                $summary->payroll_period
            );

            foreach ($computed['breakdown'] as $item) {
                $employeeDeductionId = intval($item['employee_deduction_id'] ?? 0);
                if ($employeeDeductionId <= 0) {
                    continue;
                }

                $existing = EmployeeDeductionTransaction::where('summary_id', $summary->id)
                    ->where('employee_deduction_id', $employeeDeductionId)
                    ->where('source', 'auto_payroll')
                    ->first();

                if ($existing) {
                    continue;
                }

                $record = EmployeeDeduction::find($employeeDeductionId);
                if (!$record) {
                    continue;
                }

                $actualDeductedAmount = round(floatval($item['actual_deducted_amount'] ?? 0), 2);
                if ($actualDeductedAmount <= 0) {
                    continue;
                }

                $newTotalPaid = round(floatval($record->total_paid) + $actualDeductedAmount, 2);
                $newRemaining = max(0, round(floatval($record->remaining_balance) - $actualDeductedAmount, 2));

                EmployeeDeductionTransaction::create([
                    'employee_deduction_id' => $record->id,
                    'employee_id' => $details->employee_id,
                    'summary_id' => $summary->id,
                    'sequence_no' => $summary->sequence_no,
                    'deduction_id' => $record->deduction_id,
                    'payroll_period_start' => $summary->period_start,
                    'payroll_period_end' => $summary->payroll_period,
                    'processed_date' => $summary->payment_submitted_at ? Carbon::parse($summary->payment_submitted_at)->toDateString() : Carbon::today()->toDateString(),
                    'reference_name' => $record->reference_name,
                    'scheduled_amount' => round(floatval($item['scheduled_amount'] ?? 0), 2),
                    'actual_deducted_amount' => $actualDeductedAmount,
                    'running_balance' => $newRemaining,
                    'source' => 'auto_payroll',
                    'notes' => optional($record->deduction)->name ? optional($record->deduction)->name . ' auto-deducted in payroll.' : 'Auto payroll deduction.',
                    'payroll_reference_no' => $summary->sequence_no,
                    'status' => 'posted',
                    'workstation_id' => Auth::user()->workstation_id ?? null,
                    'created_by' => Auth::user()->id ?? null,
                    'updated_by' => Auth::user()->id ?? null,
                ]);

                $record->total_paid = $newTotalPaid;
                $record->remaining_balance = $newRemaining;
                if ($record->stop_when_fully_paid && $newRemaining <= 0) {
                    $record->status = 'completed';
                }
                $record->updated_by = Auth::user()->id ?? null;
                $record->save();
            }
        }
    }

    public function getHistoryNotes($id)
    {
        $summary = PayrollSummary::where('id', $id)->firstOrFail();

        $notes = PayrollSummaryNote::query()
            ->whereNull('deleted_at')
            ->where('summary_id', $summary->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $userIds = collect([
            $summary->created_by,
            $summary->submitted_by,
            $summary->approved_by,
            $summary->payment_submitted_by,
        ])->filter()->values();

        $userIds = $userIds->merge($notes->pluck('created_by')->filter()->values())->unique()->values();

        $users = DB::table('users')
            ->select(
                'id',
                DB::raw("TRIM(CONCAT(COALESCE(firstname, ''), ' ', COALESCE(middlename, ''), ' ', COALESCE(lastname, ''))) AS full_name")
            )
            ->whereIn('id', $userIds->all())
            ->get()
            ->mapWithKeys(function ($user) {
                $fullName = trim((string) ($user->full_name ?? ''));
                return [$user->id => $fullName !== '' ? $fullName : 'User'];
            });

        $history = [];
        if ($summary->created_at) {
            $history[] = [
                'action' => 'CREATED',
                'by' => $users[$summary->created_by] ?? 'System',
                'at' => $summary->created_at,
                'description' => 'Payroll created.',
            ];
        }
        if ($summary->workflow_status == 4) {
            $history[] = [
                'action' => 'SUBMITTED_FOR_AUDIT',
                'by' => $users[$summary->updated_by] ?? 'User',
                'at' => $summary->updated_at,
                'description' => 'Payroll submitted for audit.',
            ];
        }
        if ($summary->submitted_at) {
            $history[] = [
                'action' => 'SUBMITTED_FOR_APPROVAL',
                'by' => $users[$summary->submitted_by] ?? 'User',
                'at' => $summary->submitted_at,
                'description' => 'Payroll submitted for approval.',
            ];
        }
        if ($summary->approved_at) {
            $history[] = [
                'action' => 'APPROVED',
                'by' => $users[$summary->approved_by] ?? 'User',
                'at' => $summary->approved_at,
                'description' => 'Payroll approved.',
            ];
        }
        if ($summary->payment_submitted_at) {
            $history[] = [
                'action' => 'SUBMITTED_FOR_PAYMENT',
                'by' => $users[$summary->payment_submitted_by] ?? 'User',
                'at' => $summary->payment_submitted_at,
                'description' => 'Payroll submitted for payment.',
            ];
        }

        usort($history, function ($a, $b) {
            return strtotime((string) $b['at']) <=> strtotime((string) $a['at']);
        });

        $notesPayload = $notes->map(function ($note) use ($users) {
            return [
                'id' => $note->id,
                'note' => $note->note,
                'by' => $users[$note->created_by] ?? 'User',
                'at' => $note->created_at,
            ];
        })->values();

        return response()->json([
            'history' => $history,
            'notes' => $notesPayload,
        ]);
    }

    public function addNote(Request $request)
    {
        $request->validate([
            'summary_id' => ['required', 'integer', 'exists:payroll_summaries,id'],
            'note' => ['required', 'string', 'max:1000'],
        ]);

        $user = Auth::user();

        PayrollSummaryNote::create([
            'summary_id' => (int) $request->summary_id,
            'note' => trim((string) $request->note),
            'workstation_id' => $user ? $user->workstation_id : null,
            'created_by' => $user ? $user->id : null,
            'updated_by' => $user ? $user->id : null,
        ]);

        return response()->json(['message' => 'Note added successfully.']);
    }

    private function syncPayrollToDraftExpense(PayrollSummary $summary): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $totalNetPay = (float) PayrollSummaryDetails::where('summary_id', $summary->id)
            ->whereNull('deleted_at')
            ->sum('net_pay');

        if ($totalNetPay <= 0) {
            return;
        }

        $lineAccountId = \Illuminate\Support\Facades\DB::table('chart_of_accounts as coa')
            ->leftJoin('account_types as at', 'at.id', '=', 'coa.account_type')
            ->whereNull('coa.deleted_at')
            ->where(function ($q) {
                $q->whereRaw("UPPER(TRIM(COALESCE(at.category,''))) IN ('EXPENSES','EXPENSE')")
                    ->orWhereRaw("UPPER(TRIM(COALESCE(at.account_type,''))) IN ('EXPENSE','EXPENSES','DIRECT COSTS','DEPRECIATION','OVERHEAD')");
            })
            ->orderBy('coa.id', 'asc')
            ->value('coa.id');
        $apAccountId = \Illuminate\Support\Facades\DB::table('chart_of_accounts')
            ->whereNull('deleted_at')
            ->where('system_key', 'ACCOUNTS_PAYABLE_CONTROL')
            ->value('id');

        $marker = '[AUTO_PAYROLL_SUMMARY_ID:' . $summary->id . ']';
        $billDate = $summary->pay_date ?: ($summary->period_start ?: date('Y-m-d'));
        $dueDate = $billDate;
        $description = 'Auto-created from payroll submitted for payment ' . $summary->sequence_no . ' ' . $marker;

        \Illuminate\Support\Facades\DB::transaction(function () use ($summary, $user, $totalNetPay, $lineAccountId, $marker, $billDate, $dueDate, $description) {
            $bill = AccountingBill::whereNull('deleted_at')
                ->where('description', 'like', '%' . $marker . '%')
                ->first();

            if ($bill && $bill->status !== 'DRAFT') {
                return;
            }

            if (!$bill) {
                $bill = new AccountingBill();
                $bill->bill_no = 'PYR-' . preg_replace('/[^A-Za-z0-9\-]/', '', (string) ($summary->sequence_no ?: $summary->id));
                $bill->created_by = $user->id;
            }

            $bill->bill_date = $billDate;
            $bill->due_date = $dueDate;
            $bill->description = $description;
            $bill->status = 'DRAFT';
            $bill->total_amount = $totalNetPay;
            $bill->accounts_payable_account_id = $apAccountId ?: $bill->accounts_payable_account_id;
            $bill->workstation_id = $user->workstation_id;
            $bill->updated_by = $user->id;
            $bill->save();

            AccountingBillItem::where('accounting_bill_id', $bill->id)->delete();
            AccountingBillItem::create([
                'accounting_bill_id' => $bill->id,
                'chart_of_account_id' => $lineAccountId ?: null,
                'description' => 'Payroll Expense - ' . ($summary->sequence_no ?: ('Summary #' . $summary->id)),
                'quantity' => 1,
                'unit_price' => $totalNetPay,
                'line_total' => $totalNetPay,
                'workstation_id' => $user->workstation_id,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
        });
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

                $presentDays = intval(count($details->timelogs));
                $computedAllowance = $this->getComputedAllowanceData($details, $presentDays);
                $details->allowance_days = $computedAllowance['present_days'];
                $details->allowance_payroll_basis = $computedAllowance['payroll_basis'];
                $details->allowance_payroll_basis_code = $computedAllowance['payroll_basis_code'];
                $details->allowance_employee_amount = $computedAllowance['employee_allowance_amount'];
                $details->allowance_manual_amount = $computedAllowance['manual_allowance_amount'];
                $details->allowance_amount = $computedAllowance['total_allowance_amount'];
                $details->allowance_breakdown = $computedAllowance['breakdown'];
                $details->allowance_warning = $computedAllowance['warning'];

                $computedEmployeeDeductions = $this->getComputedEmployeeDeductionData($details, $request->start, $request->end);
                $details->employee_deduction_amount = $computedEmployeeDeductions['total_amount'];
                $details->employee_deduction_breakdown = $computedEmployeeDeductions['breakdown'];

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
                    floatval($details->ca) +
                    floatval($details->employee_deduction_amount)
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
