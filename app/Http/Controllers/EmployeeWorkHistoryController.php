<?php

namespace App\Http\Controllers;

use Auth;
use App\EmployeeWorkHistory;
use Illuminate\Http\Request;

class EmployeeWorkHistoryController extends Controller
{
    public function save(Request $request, $id) {
        $output = '';

        $validate = $request->validate([
            'company' => 'required',
            'position' => 'required',
            'date_hired' => 'required',
            'date_of_resignation' => 'required',
            'remarks' => 'required'
        ]);

        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        $employment = EmployeeWorkHistory::where('employee_id', $request->employee_id)->where('date_hired', $request->date_hired)->where('company', $request->company)->count();
        if($employment === 0) {
            $output = 'saved';
            EmployeeWorkHistory::create($request->all());
        }
        else {
            $output = "updated";
            EmployeeWorkHistory::where('employee_id', $request->employee_id)->update($request->except('_token', 'created_by'));
        }
        return response()->json(compact('validate'));
    }

    public function get($id) {
        if(request()->ajax()) {
            return datatables()->of(EmployeeWorkHistory::where('employee_id', $id)->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            EmployeeWorkHistory::find($item)->delete();
        }

        return 'Record Deleted';
    }
}
