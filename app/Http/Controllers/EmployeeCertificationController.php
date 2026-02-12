<?php

namespace App\Http\Controllers;

use Auth;
use App\EmployeeCertification;

use Illuminate\Http\Request;

class EmployeeCertificationController extends Controller
{
    public function save(Request $request, $id) {
        $output = '';

        $validate = $request->validate([
            'certification_no' => 'required',
            'certification_name' => 'required',
            'certification_authority' => 'required',
            'certification_description' => 'required',
            'certification_date' => 'required',
            'certification_expiration_date' => 'required',
            'certification_level' => 'required',
            'certification_status' => 'required',
            'certification_achievements' => 'required',
            'certification_renewal_date' => 'required',
            'recertification_date' => 'required',

        ]);

        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        $employment = EmployeeCertification::where('employee_id', $request->employee_id)->where('certification_no', $request->certification_no)->count();
        if($employment === 0) {
            $output = 'saved';
            EmployeeCertification::create($request->all());
        }
        else {
            $output = "updated";
            EmployeeCertification::where('employee_id', $request->employee_id)->update($request->except('_token', 'created_by'));
        }
        return response()->json(compact('validate'));
    }

    public function get($id) {
        if(request()->ajax()) {
            return datatables()->of(EmployeeCertification::where('employee_id', $id)->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            EmployeeCertification::find($item)->delete();
        }

        return 'Record Deleted';
    }
}
