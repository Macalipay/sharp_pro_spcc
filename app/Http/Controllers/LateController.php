<?php

namespace App\Http\Controllers;

use Auth;
use App\Late;
use Illuminate\Http\Request;

class LateController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'required',
            'late' => 'required',
        ]);

        if(Late::where('employee_id', $request->employee_id)->where('date', $request->date)->count() === 0) {
            Late::create($request->all());
        }
        else {
            return "Already Exist";
        }

    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            Late::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
    
    public function get($year, $employee_id) {
        $lates = Late::with('employee')->whereYear('date', $year)->where('employee_id', $employee_id)->where('status', 0)->get();

        return response()->json(compact('lates'));
    }
}
