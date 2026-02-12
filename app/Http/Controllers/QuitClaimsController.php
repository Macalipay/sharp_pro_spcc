<?php

namespace App\Http\Controllers;

use Auth;
use App\PayrollSummary;
use App\EmployeeInformation;
use App\PayrollSummaryDetails;
use App\QuitClaims;
use App\Earnings;
use App\Deductions;
use App\QuitClaimsAdditions;
use App\QuitClaimsDeductions;
use Illuminate\Http\Request;

class QuitClaimsController extends Controller
{
    public function index()
    {
        $earnings = Earnings::get();
        $deductions = Deductions::get();

        return view('backend.pages.payroll.transaction.quit_claims', ["type"=>"full-view"], compact('earnings', 'deductions'));
    }

    public function get() {
        if (request()->ajax()) {
            return datatables()->of(
                    EmployeeInformation::with([
                        'clearance', 
                        'quit_claims', 
                        'additionals', 
                        'deductions', 
                        'compensations', 
                        'employments_tab', 
                        'absents' => function($query) {
                            $query->whereYear('date', date('Y'));
                        },
                        'absentsTimelogs' => function($query) {
                            $query->whereYear('date', date('Y'));
                        },
                        'absentsAdjustments' => function($query) {
                            $query->whereYear('date', date('Y'));
                        }
                    ])
                    ->whereIn('status', ['2', '3', '4', '5', '9'])
                    ->get()
                    ->map(function ($employee) {
                        // Get Payroll Summary Details for the employee
                        $details = PayrollSummaryDetails::join('payroll_summaries', 'payroll_summary_details.sequence_no', '=', 'payroll_summaries.sequence_no')
                            ->where('payroll_summary_details.employee_id', $employee->id)
                            ->where('payroll_summaries.schedule_type', '!=', 0)
                            ->orderBy('payroll_summaries.payroll_period', 'desc')
                            ->select('payroll_summary_details.*')
                            ->first();
                
                        // Fetch additional amounts for quit claims
                        $additions = QuitClaimsAdditions::where('employee_id', $employee->id)->sum('amount');
                        $deductions = QuitClaimsDeductions::where('employee_id', $employee->id)->sum('deduction_amount');
                
                        // Set calculated values on the employee model
                        $employee->total_additionals = $employee->additionals->sum('amount');
                        $employee->total_deductions = $employee->deductions->sum('deduction_amount');
                
                        // Set the data_load array with actual data
                        $employee->data_load = [
                            'details' => $details,
                            'month' => $employee->compensations,
                            'additions' => $additions,
                            'deductions' => $deductions
                        ];
                
                        return $employee;
                    })
                )
                ->addIndexColumn()
                ->make(true);
        }
    }
    
    public function store(Request $request)
    {
        $request['date_released'] = date('Y-m-d');
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        QuitClaims::create($request->all());

        return redirect()->back()->with('success','Successfully Added');
    }

    public function getLastPay($id) {
        $details = PayrollSummaryDetails::join('payroll_summaries', 'payroll_summary_details.sequence_no', '=', 'payroll_summaries.sequence_no')
            ->where('payroll_summary_details.employee_id', $id)
            ->where('payroll_summaries.schedule_type', '!=', 0)
            ->orderBy('payroll_summaries.payroll_period', 'desc')
            ->select('payroll_summary_details.*')
            ->first();

        $month = EmployeeInformation::with(['compensations', 'employments_tab', 'absents' => function($query) {
            $query->whereYear('date', date('Y'));
        }, 'absentsTimelogs' => function($query) {
            $query->whereYear('date', date('Y'));
        }, 'absentsAdjustments' => function($query) {
            $query->whereYear('date', date('Y'));
        }])->where('id', $id)->orderBy('id', 'desc')->first();

        $additions = QuitClaimsAdditions::where('employee_id', $id)->sum('amount');
        $deductions = QuitClaimsDeductions::where('employee_id', $id)->sum('deduction_amount');

        return response()->json(compact('details', 'month', 'additions', 'deductions'));
    }
    
}
