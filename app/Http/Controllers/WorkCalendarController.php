<?php

namespace App\Http\Controllers;

use Auth;
use App\WorkCalendar;
use Illuminate\Http\Request;

class WorkCalendarController extends Controller
{
    public function save(Request $request, $id) {
        if(WorkCalendar::where('employee_id', $request->employee_id)->count() === 0) {
            $request['created_by'] = Auth::user()->id;
            $request['updated_by'] = Auth::user()->id;
            WorkCalendar::where('employee_id', $request->employee_id)->create($request->except('_token'));
        }
        else {
            $request['updated_by'] = Auth::user()->id;
            WorkCalendar::where('employee_id', $request->employee_id)->update($request->except('_token'));
        }

        return response()->json();
    }
}
