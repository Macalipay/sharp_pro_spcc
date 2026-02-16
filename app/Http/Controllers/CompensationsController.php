<?php

namespace App\Http\Controllers;

use App\Compensations;
use App\EmployeeBenefits;
use App\EmployeeInformation;
use Auth;
use Illuminate\Http\Request;
use App\Classes\Computation\Payroll\Salary;
use App\Classes\Computation\Payroll\WithholdingTax;

class CompensationsController extends Controller
{
    public function save(Request $request, $id) {

        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;
        $request['government_mandated_benefits'] = 1;
        $request['other_company_benefits'] = 1;

        $employee_id = $request->employee_id;

        $employment = Compensations::where('employee_id', $employee_id)->count();
        if($employment === 0) {
            $output = 'saved';
            Compensations::create($request->all());
            dd('test');die();
        }
        else {
            $output = "updated";
            Compensations::where('employee_id', $employee_id)->update($request->except('_token','government_mandated', 'other_company', 'created_by'));
        }

        return $output;
    }
    
    public function getGovernmentMandatedRecord($id) {
        if(request()->ajax()) {
            return datatables()->of(EmployeeBenefits::with('benefits')->where('employee_id', $id)->where('type', 'government_mandated')->orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }
    
    public function getCompanyBenefits($id) {
        if(request()->ajax()) {
            return datatables()->of(EmployeeBenefits::with('benefits')->where('employee_id', $id)->where('type', 'other')->orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

        
    public function get($id)
    {
        $record = Compensations::where('employee_id', $id)->orderBy('id')->first();
        $employee_information = EmployeeInformation::where('id', $id)->orderBy('id')->first();

        $monthlySalary = $record !== null ? floatval($record->monthly_salary) : 0;
        $computed = $this->computeGovernmentDeductions($monthlySalary);

        return response()->json(compact('record', 'employee_information', 'computed'));
    }

    public function compute(Request $request)
    {
        $monthlySalary = floatval($request->monthly_salary ?? 0);
        $computed = $this->computeGovernmentDeductions($monthlySalary);

        return response()->json(compact('computed'));
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            EmployeeBenefits::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
    public function salary(Request $request) {

        $salary = new Salary;

        $entry = $request->entry;
        $type = $request->type;

        $data = array(
            "annual"=>$salary->annual($entry, $type),
            "monthly"=>$salary->monthly($entry, $type),
            "semi_monthly"=>$salary->semi_monthly($entry, $type),
            "weekly"=>$salary->weekly($entry, $type),
            "daily"=>$salary->daily($entry, $type),
            "hourly"=>$salary->hourly($entry, $type),
        );

        return response()->json(compact('data'));
    }

    private function computeGovernmentDeductions(float $monthlySalary): array
    {
        $sssBasis = min($monthlySalary, 35000);

        // SSS total contribution in history tab is employee 5% + employer 10% = 15%.
        $sss = $sssBasis * 0.15;

        // PAG-IBIG total contribution follows history-tab rules.
        if ($monthlySalary <= 1500) {
            $pagibig = $monthlySalary * 0.03; // 2% employee + 1% employer
        } elseif ($monthlySalary <= 10000) {
            $pagibig = $monthlySalary * 0.04; // 2% employee + 2% employer
        } else {
            $pagibig = 400; // 200 employee + 200 employer
        }

        // PhilHealth total contribution follows history-tab rules.
        if ($monthlySalary <= 10000) {
            $philhealth = 500;
        } elseif ($monthlySalary <= 100000) {
            $philhealth = $monthlySalary * 0.05;
        } else {
            $philhealth = 5000;
        }

        $withholding = (new WithholdingTax())->getValue($monthlySalary, 'monthly');
        $tax = 0;

        if ($withholding !== null) {
            $tax = (($monthlySalary - floatval($withholding->range_from)) * (floatval($withholding->rate_on_excess) * 0.01)) + floatval($withholding->fix_tax);
        }

        return [
            'sss' => round($sss, 2),
            'pagibig' => round($pagibig, 2),
            'phic' => round($philhealth, 2),
            'tax' => round($tax, 2),
        ];
    }
}
