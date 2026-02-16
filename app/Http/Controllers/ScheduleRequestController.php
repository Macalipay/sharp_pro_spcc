<?php

namespace App\Http\Controllers;

use Auth;
use App\ScheduleRequest;
use Illuminate\Http\Request;

class ScheduleRequestController extends Controller
{
    public function index()
    {
        return view('backend.pages.payroll.transaction.schedule_request', ["type" => "full-view"]);
    }

    public function get()
    {
        if (request()->ajax()) {
            return datatables()->of(
                ScheduleRequest::with('employee', 'requester', 'approver')->orderBy('id', 'desc')->get()
            )->addIndexColumn()->make(true);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'request_date' => 'required|date',
            'schedule_type' => 'required|in:monthly,semi-monthly,weekly',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'start_time' => 'required',
            'end_time' => 'required',
            'reason' => 'nullable|string',
        ]);

        $payload = array_merge($validated, [
            'request_no' => 'SRQ-' . date('YmdHis') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 4)),
            'status' => 'pending',
            'requested_by' => Auth::user()->id,
            'workstation_id' => Auth::user()->workstation_id,
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id,
        ]);

        ScheduleRequest::create($payload);

        return response()->json(['message' => 'Record Saved']);
    }

    public function edit($id)
    {
        $schedule_request = ScheduleRequest::with('employee', 'requester', 'approver')->where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('schedule_request'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'request_date' => 'required|date',
            'schedule_type' => 'required|in:monthly,semi-monthly,weekly',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'start_time' => 'required',
            'end_time' => 'required',
            'reason' => 'nullable|string',
        ]);

        ScheduleRequest::where('id', $id)->update(array_merge($validated, [
            'updated_by' => Auth::user()->id,
        ]));

        return response()->json(['message' => 'Record Saved']);
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach ($record as $item) {
            ScheduleRequest::find($item)->delete();
        }

        return 'Record Deleted';
    }

    public function approve(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:schedule_requests,id',
            'status' => 'required|in:approved,declined,pending',
            'remarks' => 'nullable|string',
        ]);

        ScheduleRequest::where('id', $request->id)->update([
            'status' => $request->status,
            'remarks' => $request->remarks,
            'approved_by' => $request->status === 'pending' ? null : Auth::user()->id,
            'approved_at' => $request->status === 'pending' ? null : date('Y-m-d H:i:s'),
            'updated_by' => Auth::user()->id,
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
