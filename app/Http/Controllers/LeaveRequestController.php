<?php

namespace App\Http\Controllers;

use Auth;
use DateTime;
use DatePeriod;
use DateInterval;
use App\ChartOfAccount;
use App\LeaveRequest;
use App\Leaves;
use App\Employment;
use App\WorkCalendar;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index()
    {
        $leaves = Leaves::with('leave_types')->get();
        $record = ChartOfAccount::orderBy('id', 'desc')->get();
        return view('backend.pages.payroll.transaction.leave_request', compact('record', 'leaves'), ["type"=>"full-view"]);
    }
    
    public function get() {
        if(request()->ajax()) {
            return datatables()->of(LeaveRequest::with('leave_type', 'employee')->orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function store(Request $request)
    {
        $pay_period = null;
        $employment = Employment::with('calendar')->where('employee_id', $request->employee_id)->first();

        $st1 = new DateTime($employment->calendar->start_date);
        $ed1 = new DateTime($employment->calendar->end_date);

        $start = new DateTime($request->start_date);
        $end = new DateTime($request->end_date);

        $st2 = (new DateTime($start->format('Y').'-'.$start->format('m').'-'.$st1->format('d')))->modify('+1 day');
        $ed2 = (new DateTime($start->format('Y').'-'.$start->format('m').'-'.$ed1->format('d')))->modify('+1 month')->modify('-1 day');

        $st3 = $ed2->modify('-1 month')->format('Y-m-d');
        $ed3 = $st2->modify('-1 month')->format('Y-m-d');


        if($start >= $st1 && $start <= $ed1) {
            $pay_period = $ed1->format('Y-m-d');
        }
        else if($start >= $st2 && $start <= $ed2) {
            $pay_period = $ed2->format('Y-m-d');
        }
        else {
            $pay_period = $ed3;
        }

        $work_calendar = WorkCalendar::where('employee_id', $request->employee_id)->first();
        $total_hours = 0;
        $day = 0;

        $validatedData = $request->validate([
            'employee_id' => 'required',
            'leave_type_id' => 'required',
            'description' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);
        
        
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($start, $interval, $end->modify('+1 day'));

        
        foreach($period as $dt) {
            if($work_calendar[strtolower($dt->format('l')."_start_time")] !== null) {
                $from = new DateTime($work_calendar[strtolower($dt->format('l')."_start_time")]);
                $to = new DateTime($work_calendar[strtolower($dt->format('l')."_end_time")]);
    
                $total_hours = $total_hours + (floatval($from->diff($to)->format('%h.%i')) - 1);
                $day = $day + 1;
            }
        }

        $current_balance = Leaves::where('employee_id', $request->employee_id)->where('leave_type', $request->leave_type_id)->first();
        
        $request['total_leave_hours'] = $day;
        $request['pay_period'] = $pay_period;
        $request['current_leave_balance'] = $current_balance->total_hours;
        $request['status'] = 0;
        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        $current = $current_balance->total_hours - $day;
    
        LeaveRequest::create($request->all());
        Leaves::where('employee_id', $request->employee_id)->where('leave_type', $request->leave_type_id)->update(['total_hours' => $current]);

        return response()->json(compact('day', 'total_hours'));
    }
    
    public function edit($id)
    {
        $leave = LeaveRequest::with('employee')->where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('leave'));
    }

    public function update(Request $request, $id)
    {
        $request['updated_by'] = Auth::user()->id;
        LeaveRequest::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            LeaveRequest::find($item)->delete();
        }
        
        return 'Record Deleted';
    }

    public function approve_leave(Request $request) {
        LeaveRequest::where('id', $request->id)->update(['status' => $request->status]);
    }
    
    public function getLeave(Request $request) {
        $leaves = Leaves::with('leave_types')->where('employee_id', $request->employee_id)->get();

        return response()->json(compact('leaves'));
    }
}
