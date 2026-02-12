<?php

namespace App\Http\Controllers;

use App\Absent;
use Illuminate\Http\Request;

class AbsentController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'required',
        ]);

        if(Absent::where('employee_id', $request->employee_id)->where('date', $request->date)->count() === 0) {
            Absent::create($request->all());
        }
        else {
            return "Already Exist";
        }

    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            Absent::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
    
    public function get($year, $employee_id) {
        $absents = Absent::with('employee')->whereYear('date', $year)->where('employee_id', $employee_id)->where('status', 0)->get();

        return response()->json(compact('absents'));
    }
}
