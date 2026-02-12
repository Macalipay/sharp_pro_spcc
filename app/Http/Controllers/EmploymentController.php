<?php

namespace App\Http\Controllers;

use App\Employment;
use Auth;
use Illuminate\Http\Request;

class EmploymentController extends Controller
{
    public function save(Request $request, $id) {
        $output = '';
        
        $validate = $request->validate([
            'classes_id' => 'required',
            'position_id' => 'required',
            'department_id' => 'required',
            'employment_date' => 'required'
        ]);

        $employment = Employment::where('employee_id', $request->employee_id)->count();
        if($employment === 0) {
            $output = 'saved';
            
            $request['created_by'] = Auth::user()->id;
            $request['updated_by'] = Auth::user()->id;
            
            Employment::create($request->all());
        }
        else {
            $output = "updated";
            
            $request['updated_by'] = Auth::user()->id;

            Employment::where('employee_id', $request->employee_id)->update($request->except('_token'));
        }
        return response()->json(compact('validate'));
    }
    
    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            Employment::where('employee_id', $item)->delete();
        }
        
        return 'Record Deleted';
    }
}
