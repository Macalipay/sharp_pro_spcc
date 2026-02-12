<?php

namespace App\Http\Controllers;

use Auth;
use App\EmployeeTraining;
use Illuminate\Http\Request;

class EmployeeTrainingController extends Controller
{
    public function save(Request $request, $id) {
        $output = '';

        $validate = $request->validate([
            'training_no' => 'required',
            'training_name' => 'required',
            'training_provider' => 'required',
            'training_description' => 'required',
            'training_date' => 'required',
            'training_location' => 'required',
            'training_duration' => 'required',
            'training_outcome' => 'required',
            'training_type' => 'required',
            'expiration_date' => 'required',

        ]);

        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        $employment = EmployeeTraining::where('employee_id', $request->employee_id)->where('training_no', $request->training_no)->count();
        if($employment === 0) {
            $output = 'saved';
            EmployeeTraining::create($request->all());
        }
        else {
            $output = "updated";
            EmployeeTraining::where('employee_id', $request->employee_id)->update($request->except('_token', 'created_by'));
        }
        return response()->json(compact('validate'));
    }

    public function get($id) {
        if(request()->ajax()) {
            return datatables()->of(EmployeeTraining::where('employee_id', $id)->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            EmployeeTraining::find($item)->delete();
        }

        return 'Record Deleted';
    }
}
