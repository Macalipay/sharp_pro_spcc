<?php

namespace App\Http\Controllers;

use Auth;
use App\PayrollCalendar;
use Illuminate\Http\Request;

class PayrollCalendarController extends Controller
{
    public function index() {
        return view('backend.pages.payroll.maintenance.payroll_calendar', ["type"=>"full-view"]);
    }
    
    public function save(Request $request) {
        $type = $request->type;
        if($type === '1' || $type === '2') {
            $validate = $request->validate([
                'title' => 'required',
                'type' => 'required',
                'start_date' => 'required',
                'end_date' => 'required',
                'payment_date' => 'required',
            ]);
        }
        else {
            $validate = $request->validate([
                'title' => 'required',
                'type' => 'required',
                'start_day' => 'required',
                'end_day' => 'required',
                'payment_day' => 'required',
            ]);
        }
        
        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        PayrollCalendar::create($request->all());

        return response()->json(compact('validate'));
    }

    public function get() {
        if(request()->ajax()) {
            return datatables()->of(PayrollCalendar::orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }
    
    public function edit($id)
    {
        $payroll_calendar = PayrollCalendar::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('payroll_calendar'));
    }

    public function update(Request $request, $id)
    {
        $request['updated_by'] = Auth::user()->id;
        PayrollCalendar::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            PayrollCalendar::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
}
