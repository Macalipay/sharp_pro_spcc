<?php

namespace App\Http\Controllers;

use App\Benefits;
use App\Compensations;
use App\EmployeeBenefits;
use App\EmployeeInformation;
use App\PayrollSummaryDetails;
use App\ChartOfAccount;
use Auth;
use Illuminate\Http\Request;

class BenefitsController extends Controller
{
    public function index()
    {
        $benefits = Benefits::orderBy('id', 'desc')->get();
        $record = ChartOfAccount::orderBy('id', 'desc')->get();
        return view('backend.pages.payroll.maintenance.benefits', compact('benefits', 'record'), ["type"=>"full-view"]);
    }

    public function get() {
        if(request()->ajax()) {
            return datatables()->of(Benefits::with('chart')->orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    
    public function sss_summary($id) {
        if(request()->ajax()) {
            return datatables()->of($this->completedPayrollBenefitRows($id, 'sss', 'SSS'))
                ->addIndexColumn()
                ->make(true);
        }
    }

    public function pagibig_summary($id) {
        if(request()->ajax()) {
            return datatables()->of($this->completedPayrollBenefitRows($id, 'pagibig', 'PAG-IBIG'))
                ->addIndexColumn()
                ->make(true);
        }
    }

    public function philhealth_summary($id) {
        if(request()->ajax()) {
            return datatables()->of($this->completedPayrollBenefitRows($id, 'philhealth', 'PHILHEALTH'))
                ->addIndexColumn()
                ->make(true);
        }
    }

    public function sss_total($id) {
        return response()->json($this->completedPayrollBenefitSummary($id, 'sss', 'sss_number'));
    }

    public function pagibig_total($id) {
        return response()->json($this->completedPayrollBenefitSummary($id, 'pagibig', 'pagibig_number'));
    }

    public function philhealth_total($id) {
        return response()->json($this->completedPayrollBenefitSummary($id, 'philhealth', 'philhealth_number'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'benefits' => ['required'],
            'description' => ['required'],
            'chart_id' => ['required']
        ]);

        
        if (!Benefits::where('benefits', $validatedData['benefits'])->exists()) {
            
            $request['workstation_id'] = Auth::user()->workstation_id;
            $request['account'] = '';
            $request['created_by'] = Auth::user()->id;
            $request['updated_by'] = Auth::user()->id;
        
            Benefits::create($request->all());
        }
        else { 
            return false;
           
        }
    }

    public function edit($id)
    {
        $benefits = Benefits::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('benefits'));
    }


    public function update(Request $request, $id)
    {
        Benefits::find($id)->update($request->all());
        return "Record Saved";
    }
    
    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            Benefits::find($item)->delete();
        }
        
        return 'Record Deleted';
    }

    public function governmentMandatedBenefits() 
    {
        $benefits = Benefits::where('type', 'government_mandated')->get();
        return response()->json(compact('benefits'));
    }

    public function otherCompanyBenefits() 
    {
        $benefits = Benefits::where('type', 'other')->get();
        return response()->json(compact('benefits'));
    }

    private function completedPayrollBenefitRows($employeeId, $column, $benefitLabel)
    {
        $rows = PayrollSummaryDetails::query()
            ->select(
                'payroll_summary_details.id',
                'payroll_summary_details.summary_id',
                'payroll_summary_details.sequence_no',
                'payroll_summary_details.created_at',
                'payroll_summaries.period_start',
                'payroll_summaries.payroll_period',
                'payroll_summaries.schedule_type',
                'payroll_summaries.workflow_status',
                'payroll_summaries.status',
                'employees.firstname as employee_firstname',
                'employees.lastname as employee_lastname'
            )
            ->selectRaw('COALESCE(payroll_summary_details.' . $column . ', 0) as amount')
            ->join('payroll_summaries', 'payroll_summaries.id', '=', 'payroll_summary_details.summary_id')
            ->leftJoin('employees', 'employees.id', '=', 'payroll_summary_details.employee_id')
            ->where('payroll_summary_details.employee_id', $employeeId)
            ->whereNull('payroll_summaries.deleted_at')
            ->where(function ($query) {
                $query->where('payroll_summaries.workflow_status', 3)
                    ->orWhere('payroll_summaries.status', 2);
            })
            ->orderBy('payroll_summaries.period_start', 'desc')
            ->orderBy('payroll_summary_details.id', 'desc')
            ->get();

        return $rows->map(function ($row) use ($benefitLabel) {
            $row->benefits = ['benefits' => $benefitLabel];
            $row->employee = [
                'firstname' => $row->employee_firstname,
                'lastname' => $row->employee_lastname,
            ];
            $row->user = null;
            return $row;
        });
    }

    private function completedPayrollBenefitSummary($employeeId, $column, $benefitNumberField)
    {
        $employee = EmployeeInformation::select($benefitNumberField)->find($employeeId);
        $compensation = Compensations::select('monthly_salary')->where('employee_id', $employeeId)->first();
        $monthlySalary = floatval(optional($compensation)->monthly_salary ?? 0);

        $totalContribution = PayrollSummaryDetails::query()
            ->join('payroll_summaries', 'payroll_summaries.id', '=', 'payroll_summary_details.summary_id')
            ->where('payroll_summary_details.employee_id', $employeeId)
            ->whereNull('payroll_summaries.deleted_at')
            ->where(function ($query) {
                $query->where('payroll_summaries.workflow_status', 3)
                    ->orWhere('payroll_summaries.status', 2);
            })
            ->sum('payroll_summary_details.' . $column);

        $employeeShare = 0;
        $employerShare = 0;
        $computedTotalContribution = 0;

        if ($column === 'sss') {
            $sssBasis = min($monthlySalary, 35000);
            $employeeShare = $sssBasis * 0.05;
            $employerShare = $sssBasis * 0.10;
            $computedTotalContribution = $employeeShare + $employerShare;
        } elseif ($column === 'pagibig') {
            if ($monthlySalary <= 1500) {
                $employeeShare = $monthlySalary * 0.02;
                $employerShare = $monthlySalary * 0.01;
            } elseif ($monthlySalary <= 10000) {
                $employeeShare = $monthlySalary * 0.02;
                $employerShare = $monthlySalary * 0.02;
            } else {
                $employeeShare = 200;
                $employerShare = 200;
            }
            $computedTotalContribution = $employeeShare + $employerShare;
        } elseif ($column === 'philhealth') {
            if ($monthlySalary <= 10000) {
                $computedTotalContribution = 500;
            } elseif ($monthlySalary <= 100000) {
                $computedTotalContribution = $monthlySalary * 0.05;
            } else {
                $computedTotalContribution = 5000;
            }
            $employeeShare = $computedTotalContribution / 2;
            $employerShare = $computedTotalContribution / 2;
        }

        return [
            'benefit_number' => optional($employee)->{$benefitNumberField},
            'monthly_salary' => $monthlySalary,
            'employee_share' => round(floatval($employeeShare), 2),
            'employer_share' => round(floatval($employerShare), 2),
            'total_contribution' => round(floatval($computedTotalContribution), 2),
            'completed_employee_contribution_total' => round(floatval($totalContribution), 2),
        ];
    }
}
