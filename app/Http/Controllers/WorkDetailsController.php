<?php

namespace App\Http\Controllers;

use Auth;
use DateTime;
use App\WorkDetails;
use App\WorkCalendar;
use App\TimeLogs;
use App\OvertimeRequest;
use Illuminate\Http\Request;

class WorkDetailsController extends Controller
{
    public function save(Request $request) {
        WorkDetails::create($request->all());

        $calendar = WorkCalendar::where('employee_id', $request->employee_id)->first();

        if($calendar !== null) {
            if($request->earnings === "OT") {
                    $logs = TimeLogs::where('id', $request->time_logs_id)->first();

                    $days = strtolower(date('l', strtotime($logs->date)));
                    $start = $logs->date." ".$calendar[$days."_end_time"].":00";

                    $start_dt = new DateTime($start);
                    $end_dt = new DateTime($logs->time_out);

                    $interval = $start_dt->diff($end_dt);
                    $totalHours = ($interval->days * 24) + $interval->h + ($interval->i / 60);

                    $ot_data = array(
                        "employee_id" => $request->employee_id,
                        "ot_date" => $logs->date,
                        "start_time" => $start,
                        "end_time" => $logs->time_out,
                        "total_hours" => $totalHours ,
                        "reason" => $request->remarks,
                        "status" => 'pending'
                    );

                    OvertimeRequest::create($ot_data);
            }
        }
        else {
            $logs = TimeLogs::where('id', $request->time_logs_id)->first();
            
            $start_dt = new DateTime($logs->time_in);
            $end_dt = new DateTime($logs->time_out);

            $interval = $start_dt->diff($end_dt);
            $totalHours = ($interval->days * 24) + $interval->h + ($interval->i / 60);

            $ot_data = array(
                "employee_id" => $request->employee_id,
                "ot_date" => $logs->date,
                "start_time" => $logs->time_in,
                "end_time" => $logs->time_out,
                "ot_type" => "OTR",
                "total_hours" => $totalHours ,
                "reason" => $request->remarks,
                "status" => 'pending'
            );

            OvertimeRequest::create($ot_data);
        }
    }

    public function get($id) {
        $workdetails = WorkDetails::with('worktype', 'employee', 'projects')->where('time_logs_id', $id)->get();

        return response()->json(compact('workdetails'));
    }
    
    public function edit($id)
    {
        $workdetails = WorkDetails::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('workdetails'));
    }

    public function update(Request $request, $id)
    {
        WorkDetails::find($id)->update($request->all());
        return "Record Saved";
    }
    
    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            WorkDetails::find($item)->delete();
        }

        return 'Record Deleted';
    }
}
