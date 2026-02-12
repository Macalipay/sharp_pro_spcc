<?php

namespace App\Http\Controllers;

use App\Clearance;
use Illuminate\Http\Request;

class ClearanceController extends Controller
{
    public function save(Request $request) {
        
        if(Clearance::where('employee_id', $request->employee_id)->count() === 0) {
            $clearance = Clearance::create($request->all());
        }
        else {
            $clearance = Clearance::where('employee_id', $request->employee_id)->update($request->all());
        }

    }

    public function get($id) {
        $clearance = Clearance::where('employee_id', $id)->first();

        return response()->json(compact('clearance'));
    }
}
