<?php

namespace App\Http\Controllers;

use App\DeductionSetup;
use Illuminate\Http\Request;

class DeductionSetupController extends Controller
{
    public function save(Request $request) {
        $deduction = DeductionSetup::create($request->all());
    }
    
    public function destroy(Request $request) {
        $deduction = DeductionSetup::where('deduction_id', $request->deduction_id)->where('employee_id', $request->employee_id)->delete();
    }
    
    public function get($id) {
        $deduction = DeductionSetup::where('employee_id', $id)->get();

        return response()->json(compact('deduction'));
    }
}
