<?php

namespace App\Http\Controllers;

use DateTime;
use App\EmployeeInformation;
use App\TimeLogs;
use App\ImageUpload;
use App\OvertimeRequest;
use App\WorkTypeSetup;
use App\WorkDetails;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function getScreen($type)
    {
        if($type === "admin" || $type === "site") {
            return view('frontend.pages.attendance', compact('type'));
        }
        else {
            abort(404);
        }
    }
    
    public function get($type) {
        if(request()->ajax()) {
            return datatables()->of(TimeLogs::with('employee', 'image')->where('date', date('Y-m-d'))->where('log_type', $type)->orderBy('date','asc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function get_employee($rfid) {
        $record = EmployeeInformation::where('rfid', $rfid)->where('status', 1)->first();

        return response()->json(compact('record'));
    }
    
    public function store(Request $request)
    {
        $data = array(
            'employee_id' => $request->employee_id,
            'date' => date('Y-m-d'),
            'time_in' => $request->type === "time_in"?date('Y-m-d H:i:s'):null,
            'break_out' => $request->type === "break_out"?date('Y-m-d H:i:s'):null,
            'break_in' => $request->type === "break_in"?date('Y-m-d H:i:s'):null,
            'time_out' => $request->type === "time_out"?date('Y-m-d H:i:s'):null,
            'total_hours' => 0,
            'break_hours' => 0,
            'ot_hours' => 0,
            'late_hours' => 0,
            'undertime' => 0,
            'type' => 1,
            'workstation_id' => 1,
            'created_by' => 1,
            'updated_by' => 1,
            'log_type' => $request->log_type,
            'status' => 1
        );

        // $get = TimeLogs::where('employee_id', $request->employee_id)->where('date', date('Y-m-d'))->whereNull($request->type)->get();
    
        if(TimeLogs::where('employee_id', $request->employee_id)->where('date', date('Y-m-d'))->count() !== 0) {
            if(TimeLogs::where('employee_id', $request->employee_id)->where('date', date('Y-m-d'))->whereNull($request->type)->count() !== 0) {
                TimeLogs::where('employee_id', $request->employee_id)->where('date', date('Y-m-d'))->update([$request->type => date('Y-m-d H:i:s')]);
                $attendance = TimeLogs::where('employee_id', $request->employee_id)->where('date', date('Y-m-d'))->firstOrFail();

                if($request->type === "ot_in") {
                    $ot = array(
                        "employee_id" => $request->employee_id,
                        "ot_date" => date('Y-m-d'),
                        "start_time" =>  $request->type === "ot_in"?date('Y-m-d H:i:s'):null,
                        "end_time" => null,
                        "total_hours" => null,
                        "reason" => null,
                        "status" => "pending"
                    );

                    OvertimeRequest::create($ot);
                }
                else if($request->type === "ot_out") {
                    $ot_r = OvertimeRequest::where('employee_id', $request->employee_id)->where('ot_date', date('Y-m-d'))->first();

                    $s1 = new DateTime($ot_r->start_time);
                    $e1 = new DateTime(date('Y-m-d H:i:s'));
                    $interval = $s1->diff($e1);

                    $totalHours = ($interval->days * 24) + $interval->h + ($interval->i / 60);

                    OvertimeRequest::where('employee_id', $request->employee_id)->where('ot_date', date('Y-m-d'))->update(['end_time' => date('Y-m-d H:i:s'), "total_hours" => $totalHours]);
                }

                // $file = $request->picture;  // your base64 encoded
                // $explode = explode(",", $file);
                // $ext = explode(";", $explode[0]);
                // $ext = explode("/", $ext[0]);

                // // $file = str_replace('data:image/png;base64,', '', $file);
                // $directoryPath = storage_path('app/public/attendance');

                // $file = str_replace(' ', '+', $explode[1]);
                // $fileName = str_random(10).'_'.$attendance->id.'.'.$ext[1];

                // if (!\File::exists($directoryPath)) {
                //     \File::makeDirectory($directoryPath, 0755, true, true);
                //     \File::put(storage_path(). '/app/public/attendance/' . $fileName, base64_decode($file));
                // }
                // else {

                //     \File::put(storage_path(). '/app/public/attendance/' . $fileName, base64_decode($file));

                // }

                // try {
                //     $data_access = array(
                //         "timelog_id" => $attendance->id,
                //         "type" => $request->type,
                //         "filename" => $fileName,
                //         "status" => 1
                //     );
    
                //     ImageUpload::create($data_access);
                // } catch(Exception $e) {

                // }

                $message = 'Saved';
            }
            else {
                $message = 'Already exist';
            }
        }
        else {
            $get = TimeLogs::where('employee_id', $request->employee_id)->orderBy('id','desc')->first();

            if($request->type === 'time_out' || $request->type === 'ot_in' || $request->type === 'ot_out') {
                $attendance = TimeLogs::where('date', $get->date)->update(['time_out'=>date('Y-m-d H:i:s')]);
                
                if($request->type === "ot_in") {
                    $ot = array(
                        "employee_id" => $request->employee_id,
                        "ot_date" => $get->date,
                        "start_time" =>  $request->type === "ot_in"?date('Y-m-d H:i:s'):null,
                        "end_time" => null,
                        "total_hours" => null,
                        "reason" => null,
                        "status" => "pending"
                    );

                    OvertimeRequest::create($ot);
                }
                else if($request->type === "ot_out") {
                    $ot_r = OvertimeRequest::where('employee_id', $request->employee_id)->where('ot_date', $get->date)->first();

                    $s1 = new DateTime($ot_r->start_time);
                    $e1 = new DateTime(date('Y-m-d H:i:s'));
                    $interval = $s1->diff($e1);

                    $totalHours = ($interval->days * 24) + $interval->h + ($interval->i / 60);

                    OvertimeRequest::where('employee_id', $request->employee_id)->where('ot_date', $get->date)->update(['end_time' => date('Y-m-d H:i:s'), "total_hours" => $totalHours]);
                }
                $text = $get->id;
            }
            else {
                $attendance = TimeLogs::create($data);
                $text = $attendance->id;
            }

            $worktype_setup = WorkTypeSetup::where('employee_id', $request->employee_id)->where('days', strtolower(date('l')))->get();

            foreach($worktype_setup as $item) {
                $work_data = array(
                    "employee_id" => $item->employee_id,
                    "worktype_id" => $item->worktype_id,
                    "earnings" => $item->earnings,
                    "project_id" => $item->project_id,
                    "hours" => $item->hours,
                    "remarks" => $item->remarks,
                    "time_logs_id" => $text,
                );

                WorkDetails::create($work_data);
            }

            // $file = $request->picture;  // your base64 encoded
            // $explode = explode(",", $file);
            // $ext = explode(";", $explode[0]);
            // $ext = explode("/", $ext[0]);

            // // $file = str_replace('data:image/png;base64,', '', $file);

            // $directoryPath = storage_path('app/public/attendance');

            // $file = str_replace(' ', '+', $explode[1]);
            // $fileName = str_random(10).'_'.$text.'.'.$ext[1];
           
            // if (!\File::exists($directoryPath)) {
            //     \File::makeDirectory($directoryPath, 0755, true, true);
            //     \File::put(storage_path(). '/app/public/attendance/' . $fileName, base64_decode($file));
            // }
            // else {

            //     \File::put(storage_path(). '/app/public/attendance/' . $fileName, base64_decode($file));

            // }

            // $data_access = array(
            //     "timelog_id" => $text,
            //     "type" => $request->type,
            //     "filename" => $fileName,
            //     "status" => 1
            // );

            // ImageUpload::create($data_access);
            

            $message = 'Saved';
        }


        return response()->json(compact('data', 'message'));
    }
}
