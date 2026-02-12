<?php

namespace App\Http\Controllers;

use Auth;
use App\AllowanceSetup;
use Illuminate\Http\Request;

class AllowanceSetupController extends Controller
{
    public function save(Request $request) {
        
        $allownce_validate = $request->validate([
            'allowance_id' => ['required'],
            'amount' => ['required'],
            'days' => ['required'],
        ]);

        $allowance_data = AllowanceSetup::where('allowance_id', $request->allowance_id)->where('employee_id', $request->employee_id)->where('sequence_no', $request->sequence_no)->first();
        if($allowance_data) {
            $allowance = AllowanceSetup::where('id', $allowance_data->id)->update(['amount' => ($allowance_data->amount + $request->amount),'days' => ($allowance_data->days + $request->days)]);
        }
        else {
            $allowance = AllowanceSetup::create($request->all());
        }
    }
    
    public function destroy(Request $request) {
        $allowance = AllowanceSetup::where('id', $request->id)->delete();
    }
    
    public function get($id) {
        $allowance = AllowanceSetup::where('employee_id', $id)->get();

        return response()->json(compact('allowance'));
    }

    public function edit($id)
    {
        $allowance = AllowanceSetup::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('allowance'));
    }

    public function update(Request $request, $id)
    {
        $request['updated_by'] = Auth::user()->id;
        AllowanceSetup::find($id)->update($request->all());
        return "Record Saved";
    }
    
    public function getBySequence($id, $emp_id) {
        $allowance = AllowanceSetup::with('allowances')->where('sequence_no', $id)->where('employee_id', $emp_id)->get();

        if(request()->ajax()) {
            return datatables()->of($allowance)
            ->addIndexColumn()
            ->make(true);
        }
    }
}
