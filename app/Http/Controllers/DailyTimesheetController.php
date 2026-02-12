<?php

namespace App\Http\Controllers;

use Auth;
use App\Project;
use App\TimeLogs;
use App\WorkCalendar;
use App\WorkType;
use App\WorkDetails;
use Illuminate\Http\Request;

class DailyTimesheetController extends Controller
{
    public function index() {
        $projects = Project::get();
        $worktype = WorkType::get();

        return view('backend.pages.payroll.transaction.daily_timesheet', ['type' => 'full-view'], compact('projects', 'worktype'));
    }
    
    public function get($date, $project) {
        if(request()->ajax()) {
            $query = TimeLogs::with('employee', 'workdetails', 'workdetails.worktype');

            if ($project !== "all") {
                $query->whereHas('project', function ($query) use ($project) {
                    $query->where('project_id', $project);
                });
            }

            $timeLogs = $query->where('date', $date)->get()->map(
                function($timelogs) use($date) {
                    $timelogs->day = date('l', strtotime($date));
                    $timelogs->rendered = WorkDetails::where('time_logs_id', $timelogs->id)->sum('hours');
                    $timelogs->schedule = WorkCalendar::where('employee_id', $timelogs->employee_id)->first();

                    return $timelogs;
                }
            );
            
            return datatables()->of($timeLogs)
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function edit($id)
    {
        $timelogs = TimeLogs::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('timelogs'));
    }
    
    public function update(Request $request, $id)
    {   
        TimeLogs::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            TimeLogs::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
}
