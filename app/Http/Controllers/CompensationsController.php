<?php

namespace App\Http\Controllers;

use App\Compensations;
use App\EmployeeBenefits;
use App\EmployeeInformation;
use Auth;
use Illuminate\Http\Request;
use App\Classes\Computation\Payroll\Salary;

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
        return response()->json(compact('record', 'employee_information'));
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
}
