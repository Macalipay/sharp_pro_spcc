<?php

namespace App\Http\Controllers;

use Auth;
use App\EmployeeInformation;
use App\Absent;
use App\Late;
use App\PayMonth;
use App\PayrollSummary;
use App\Holiday;
use App\Employment;
use App\TimeLogs;
use App\LeaveRequest;
use App\Compensations;
use App\PayrollSummaryDetails;
use Illuminate\Http\Request;

class PayMonthController extends Controller
{
    public function index()
    {
        $summary = PayrollSummary::where('sequence_no', '13-'.date('Y'))->count();
        return view('backend.pages.payroll.transaction.pay_month', ["type"=>"full-view"], compact('summary'));
    }

    public function get($year) {
        if(request()->ajax()) {
            return datatables()->of(EmployeeInformation::with(['compensations', 'employments_tab', 'absents' => function($query) use($year) {
                $query->whereYear('date', $year);
            }, 'absentsTimelogs' => function($query) use($year) {
                $query->whereYear('date', $year);
            }, 'absentsAdjustments' => function($query) use($year) {
                $query->whereYear('date', $year);
            },
            'leaves' => function ($query) use ($year) {
                $query->whereYear('pay_period', $year);
            }])->where('status', 1)->where('employment_type', 'monthly_rate')->orderBy('id', 'desc')->get()
            ->map(function ($employee, $year) {

                $employee->total_leaves = $employee->leaves->where('status', 1)->sum('total_leave_hours');
                $employee->total_holidays = Holiday::whereBetween('date', [$employee->employments_tab->employment_date, date('Y-m-d')])->count();
                $employee->total_lates = $employee->lates->sum('late');
                $employee->total_lates_logs = $employee->latesTimelogs->sum('late');
                $employee->total_lates_adjustments = $employee->latesAdjustments->sum('late');

                return $employee;
            }))
            ->addIndexColumn()
            ->make(true);
        }
    }
    
    public function getFixed($year) {
        if(request()->ajax()) {
            return datatables()->of(EmployeeInformation::with(['compensations', 'employments_tab', 'absents' => function($query) use($year) {
                $query->whereYear('date', $year);
            }, 'absentsTimelogs' => function($query) use($year) {
                $query->whereYear('date', $year);
            }, 'absentsAdjustments' => function($query) use($year) {
                $query->whereYear('date', $year);
            }])->where('status', 1)->where('employment_type', 'fixed_rate')->orderBy('id', 'desc')->get()
            ->map(function ($employee, $year) {
                $employee->total_lates = $employee->lates->sum('late');
                $employee->total_lates_logs = $employee->latesTimelogs->sum('late');
                $employee->total_lates_adjustments = $employee->latesAdjustments->sum('late');
                
                return $employee;
            })
            )
            ->addIndexColumn()
            ->make(true);
        }
    }
    
    public function getDaily($year) {
        if(request()->ajax()) {
            return datatables()->of(EmployeeInformation::with(['compensations', 'employments_tab', 'absents' => function($query) use($year) {
                $query->whereYear('date', $year);
            }, 'absentsTimelogs' => function($query) use($year) {
                $query->whereYear('date', $year);
            }, 'absentsAdjustments' => function($query) use($year) {
                $query->whereYear('date', $year);
            },  
            'timelogs' => function($query) use($year) {
                $query->whereYear('date', $year);
            },
            'leaves' => function ($query) use ($year) {
                $query->whereYear('pay_period', $year);
            }])->where('status', 1)->where('employment_type', 'daily_rate')->orderBy('id', 'desc')->get()
            ->map(function ($employee, $year) {
                $employee->total_leaves = $employee->leaves->where('status', 1)->sum('total_leave_hours');
                $employee->total_lates = $employee->lates->sum('late');
                $employee->total_lates_logs = $employee->latesTimelogs->sum('late');
                $employee->total_lates_adjustments = $employee->latesAdjustments->sum('late');
                
                return $employee;
            }))
            ->addIndexColumn()
            ->make(true);
        }
    }
    
    public function get_absents($year, $employee_id) {
        if(request()->ajax()) {
            return datatables()->of(Absent::with('employee')->whereYear('date', $year)->where('employee_id', $employee_id)->where('status', 1)->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function get_lates($year, $employee_id) {
        if(request()->ajax()) {
            return datatables()->of(Late::with('employee')->whereYear('date', $year)->where('employee_id', $employee_id)->where('status', 1)->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function release(Request $request) {

        if($request->fixed !== null) {
            $summary = array(
                "sequence_no" => $request->fixed['sequence_no'],
                "schedule_type" => $request->fixed['schedule_type'],
                "period_start" => $request->fixed['period_start'],
                "payroll_period" => $request->fixed['payroll_period'],
                "pay_date" => $request->fixed['pay_date'],
                "status" => $request->fixed['status'],
                "workstation_id" => Auth::user()->workstation_id,
                "created_by" => Auth::user()->id,
                "updated_by" => Auth::user()->id
            );
    
            $summary = PayrollSummary::create($summary);
    
            foreach($request->fixed['employee'] as $item) {
                $details = array(
                    "employee_id" => $item['employee_id'],
                    "sequence_no" => $request->fixed['sequence_no'],
                    "gross_earnings" => $item['pay_total'],
                    "sss" => 0,
                    "pagibig" => 0,
                    "philhealth" => 0,
                    "tax" => 0,
                    "net_pay" => $item['pay_total'],
                    "status" => 1,
                    "workstation_id" => Auth::user()->workstation_id,
                    "created_by" => Auth::user()->id,
                    "updated_by" => Auth::user()->id
                );
    
                PayrollSummaryDetails::create($details);
            }
        }
        
        if($request->daily !== null) {
            $summary = array(
                "sequence_no" => $request->daily['sequence_no'],
                "schedule_type" => $request->daily['schedule_type'],
                "period_start" => $request->daily['period_start'],
                "payroll_period" => $request->daily['payroll_period'],
                "pay_date" => $request->daily['pay_date'],
                "status" => $request->daily['status'],
                "workstation_id" => Auth::user()->workstation_id,
                "created_by" => Auth::user()->id,
                "updated_by" => Auth::user()->id
            );
    
            $summary = PayrollSummary::create($summary);
    
            foreach($request->daily['employee'] as $item) {
                $details = array(
                    "employee_id" => $item['employee_id'],
                    "sequence_no" => $request->daily['sequence_no'],
                    "gross_earnings" => $item['pay_total'],
                    "sss" => 0,
                    "pagibig" => 0,
                    "philhealth" => 0,
                    "tax" => 0,
                    "net_pay" => $item['pay_total'],
                    "status" => 1,
                    "workstation_id" => Auth::user()->workstation_id,
                    "created_by" => Auth::user()->id,
                    "updated_by" => Auth::user()->id
                );
    
                PayrollSummaryDetails::create($details);
            }
        }
        
        if($request->monthly !== null) {
            $summary = array(
                "sequence_no" => $request->monthly['sequence_no'],
                "schedule_type" => $request->monthly['schedule_type'],
                "period_start" => $request->monthly['period_start'],
                "payroll_period" => $request->monthly['payroll_period'],
                "pay_date" => $request->monthly['pay_date'],
                "status" => $request->monthly['status'],
                "workstation_id" => Auth::user()->workstation_id,
                "created_by" => Auth::user()->id,
                "updated_by" => Auth::user()->id
            );
    
            $summary = PayrollSummary::create($summary);
    
            foreach($request->monthly['employee'] as $item) {
                $details = array(
                    "employee_id" => $item['employee_id'],
                    "sequence_no" => $request->monthly['sequence_no'],
                    "gross_earnings" => $item['pay_total'],
                    "sss" => 0,
                    "pagibig" => 0,
                    "philhealth" => 0,
                    "tax" => 0,
                    "net_pay" => $item['pay_total'],
                    "status" => 1,
                    "workstation_id" => Auth::user()->workstation_id,
                    "created_by" => Auth::user()->id,
                    "updated_by" => Auth::user()->id
                );
    
                PayrollSummaryDetails::create($details);
            }
        }
    }

    public function getDailyLogs($year, $id) {
        $timelogs = TimeLogs::selectRaw('employee_id, MONTH(date) as month, COUNT(*) as total_timelogs')
            ->where('employee_id', $id)
            ->whereYear('date', $year)
            ->groupBy('employee_id', 'month')
            ->get()
            ->keyBy('month');

        $leave = LeaveRequest::getDaysPerMonth($id, $year);

        $monthlyReport = [];

        foreach ($timelogs as $log) {
            $month = $log->month;
            $monthlyReport[$month]['total_timelogs'] = $log->total_timelogs;
            $monthlyReport[$month]['total_leave_days'] = isset($leave[$month]) ? $leave[$month] : 0; 
        }

        foreach ($leave as $month => $days) {
            if (!isset($monthlyReport[$month])) {
                $monthlyReport[$month]['total_timelogs'] = 0;
                $monthlyReport[$month]['total_leave_days'] = $days;
            }
        }

        return response()->json($monthlyReport);
    }
    
    public function getMonthlyLogs($year, $id) {
        $leave = LeaveRequest::getDaysPerMonth($id, $year);
        $compensation = Compensations::where('employee_id', $id)->first();
        $employment = Employment::where('employee_id', $id)->first();
        $holiday = Holiday::whereBetween('date', [$employment->employment_date, date('Y-m-d')])->get();

        $absents = Absent::with('employee')->whereYear('date', $year)->where('employee_id', $id)->where('status', 0)->get();

        $monthlyReport = [];

        foreach ($leave as $month => $days) {
            if (!isset($monthlyReport[$month])) {
                $monthlyReport[$month]['total_leave_days'] = $days;
            }
        }

        return response()->json(compact('monthlyReport', 'compensation', 'absents', 'holiday'));
    }
}
