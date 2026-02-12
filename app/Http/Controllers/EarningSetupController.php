<?php

namespace App\Http\Controllers;

use App\EarningSetup;
use Illuminate\Http\Request;

class EarningSetupController extends Controller
{
    public function save(Request $request) {
        $earning = EarningSetup::create($request->all());
    }
    
    public function destroy(Request $request) {
        $earning = EarningSetup::where('earning_id', $request->earning_id)->where('employee_id', $request->employee_id)->delete();
    }
    
    public function get($id) {
        $earning = EarningSetup::where('employee_id', $id)->get();

        return response()->json(compact('earning'));
    }
}
