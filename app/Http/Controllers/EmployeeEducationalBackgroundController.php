<?php

namespace App\Http\Controllers;
use Auth;

use App\EmployeeEducationalBackground;
use Illuminate\Http\Request;

class EmployeeEducationalBackgroundController extends Controller
{
    public function save(Request $request, $id) {
        $output = '';

        $validate = $request->validate([
            'educational_attainment' => 'required',
            'course' => 'required',
            'school_year' => 'required',
            'school' => 'required'
        ]);

        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        $employment = EmployeeEducationalBackground::where('employee_id', $request->employee_id)->where('school_year', $request->school_year)->count();
        if($employment === 0) {
            $output = 'saved';
            EmployeeEducationalBackground::create($request->all());
        }
        else {
            $output = "updated";
            EmployeeEducationalBackground::where('employee_id', $request->employee_id)->update($request->except('_token', 'created_by'));
        }
        return response()->json(compact('validate'));
    }

    public function get($id) {
        if(request()->ajax()) {
            return datatables()->of(EmployeeEducationalBackground::where('employee_id', $id)->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            EmployeeEducationalBackground::find($item)->delete();
        }

        return 'Record Deleted';
    }
}
