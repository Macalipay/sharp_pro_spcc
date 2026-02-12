<?php

namespace App\Http\Controllers;

use DateTime;
use Auth;
use App\OvertimeRequest;
use Illuminate\Http\Request;

class OvertimeRequestController extends Controller
{
    public function index()
    {
        return view('backend.pages.payroll.transaction.overtime_request', ["type"=>"full-view"]);
    }
    
    public function get() {
        if(request()->ajax()) {
            return datatables()->of(OvertimeRequest::with('employee')->orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }
    
    public function get_filter($start, $end) {
        if(request()->ajax()) {
            return datatables()->of(OvertimeRequest::with('employee')->whereBetween('ot_date', [$start, $end])->orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'employee_id' => 'required',
            'ot_date' => 'required',
            'start_time' => 'required',
            'end_time' => 'required'
        ]);

        $s1 = new DateTime($request->start_time);
        $e1 = new DateTime($request->end_time);
        $interval = $s1->diff($e1);

        $totalHours = ($interval->days * 24) + $interval->h + ($interval->i / 60);
        $request['total_hours'] = $totalHours;
    
        OvertimeRequest::create($request->all());

    }
    
    public function edit($id)
    {
        $overtime = OvertimeRequest::with('employee')->where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('overtime'));
    }

    public function update(Request $request, $id)
    {
        OvertimeRequest::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            OvertimeRequest::find($item)->delete();
        }
        
        return 'Record Deleted';
    }

    public function approve_leave(Request $request) {
        OvertimeRequest::where('id', $request->id)->update(['status' => $request->status, 'approved_by' => Auth::user()->id, 'approved_at' => date('Y-m-d H:i:s')]);
    }
}
