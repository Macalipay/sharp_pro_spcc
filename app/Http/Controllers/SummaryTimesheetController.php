<?php

namespace App\Http\Controllers;

use Auth;
use DB;
use App\Late;
use App\Absent;
use App\Project;
use App\WorkType;
use Illuminate\Http\Request;

class SummaryTimesheetController extends Controller
{
    public function index() {
        $projects = Project::get();
        $worktype = WorkType::get();

        return view('backend.pages.payroll.transaction.summary_timesheet', ['type' => 'full-view'], compact('projects', 'worktype'));
    }

    public function get() {
        $results = DB::table('projects')
            ->select('projects.project_name', 'work_types.name', 'projects.id as project_id', 'work_types.id as work_type_id')
            ->join('project_taggings', 'project_taggings.project_id', '=', 'projects.id')
            ->join('work_details', 'work_details.employee_id', '=', 'project_taggings.employee_id')
            ->join('work_types', 'work_details.worktype_id', '=', 'work_types.id')
            ->orderBy('projects.project_name', 'asc')
            ->groupBy('project_id', 'work_type_id')
            ->get()->map(function($results) {

                $results->employees = DB::table('work_details')
                ->select('*', DB::raw('SUM(work_details.hours) AS total_working_hours'))
                ->join('project_taggings', 'project_taggings.employee_id', '=', 'work_details.employee_id')
                ->join('employees', 'employees.id', '=', 'work_details.employee_id')
                ->where('work_details.worktype_id', $results->work_type_id)
                ->where('project_taggings.project_id', $results->project_id)
                ->groupBy('employees.id', 'work_details.worktype_id')
                ->get()->map(function($results) {
                    $results->total_absents = Absent::where('employee_id', $results->id)->count();
                    $results->total_late = Late::where('employee_id', $results->id)->sum('late');

                    return $results;
                });

                return $results;
            });

        return response()->json(compact('results'));
    }
    
    public function getRecord(Request $request) {
        $results = DB::table('projects')
            ->select('projects.project_name', 'work_types.name', 'projects.id as project_id', 'work_types.id as work_type_id')
            ->join('project_taggings', 'project_taggings.project_id', '=', 'projects.id')
            ->join('work_details', 'work_details.employee_id', '=', 'project_taggings.employee_id')
            ->join('work_types', 'work_details.worktype_id', '=', 'work_types.id')
            ->orderBy('projects.project_name', 'asc')
            ->groupBy('project_id', 'work_type_id');

        if($request->project_id !== null) {
            $results = $results->where('projects.id', '=', $request->project_id);
        }

        if($request->worktype_id !== null) {
            $results = $results->where('work_types.id', '=', $request->worktype_id);
        }

        $results = $results->get()->map(function($results) use($request){

            $results->employees = DB::table('work_details')
                ->select('*', DB::raw('SUM(work_details.hours) AS total_working_hours'))
                ->join('project_taggings', 'project_taggings.employee_id', '=', 'work_details.employee_id')
                ->join('employees', 'employees.id', '=', 'work_details.employee_id')
                ->join('time_logs', 'time_logs.id', '=', 'work_details.time_logs_id')
                ->where('work_details.worktype_id', $results->work_type_id)
                ->where('project_taggings.project_id', $results->project_id)
                ->groupBy('employees.id', 'work_details.worktype_id');
            
            if($request->start_date !== null && $request->end_date !== null) {
                $results->employees = $results->employees->whereBetween('time_logs.date', [$request->start, $request->end]);
            }

            $results->employees = $results->employees->get()->map(function($employees) use($request){
                $employees->total_absents = Absent::where('employee_id', $employees->employee_id);
                
                if($request->start_date !== null && $request->end_date !== null) {
                    $employees->total_absents = $employees->total_absents->whereBetween('date', [$request->start, $request->end]);
                }
                $employees->total_absents = $employees->total_absents->whereBetween('date', [$request->start, $request->end])->count();

                $employees->total_late = Late::where('employee_id', $employees->employee_id);
                if($request->start_date !== null && $request->end_date !== null) {
                    $employees->total_late = $employees->total_late->whereBetween('date', [$request->start, $request->end]);
                }
                $employees->total_late = $employees->total_late->whereBetween('date', [$request->start, $request->end])->sum('late');

                return $employees;
            });

            return $results;
        });

        return response()->json(compact('results'));
    }
}
