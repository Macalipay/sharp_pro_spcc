<?php

namespace App\Http\Controllers;

use Auth;
use App\Schedulings;
use App\WorkAssignments;
use App\Earnings;
use App\Departments;
use App\EmployeeInformation;
use App\Classes\TimeKeeping\Scheduling;
use Illuminate\Http\Request;

class SchedulingsController extends Controller
{
    public function index()
    {
        $work_assignments = WorkAssignments::get();
        $earnings = Earnings::get();
        $departments = Departments::get();
        return view('backend.pages.transaction.timekeeping.scheduling', ["type"=>"full-view"], compact('work_assignments', 'earnings', 'departments'));
    }

    public function save(Request $request)
    {
        if($request->type === "1") {
            $request['start_time'] = null;
            $request['end_time'] = null;
            $validate = $request->validate([
                'type' => 'required',
                'type_description' => 'required'
            ]);
        }
        else {
            $validate = $request->validate([
                'type' => 'required',
                'start_time' => 'required',
                'end_time' => 'required',
                'work_assignment_id' => 'required',
                'earning_id' => 'required',
            ]);
        }

        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        Schedulings::create($request->all());

        return response()->json(compact('validate'));
    }

    public function get($department, $first, $last) {
        $scheduling = new Scheduling;
        if(request()->ajax()) {
            if($department === "all") {
                $employee_schedule = EmployeeInformation::selectRaw($scheduling->query($first, $last))
                ->leftJoin('schedulings', 'employees.id', '=', 'schedulings.employee_id')
                ->leftJoin('work_assignments', 'work_assignments.id', '=', 'schedulings.work_assignment_id')
                ->leftJoin('earnings', 'earnings.id', '=', 'schedulings.earning_id')
                ->groupBy("employees.id")
                ->get();
            }
            else {
                $employee_schedule = EmployeeInformation::selectRaw($scheduling->query($first, $last))
                ->leftJoin('schedulings', 'employees.id', '=', 'schedulings.employee_id')
                ->leftJoin('work_assignments', 'work_assignments.id', '=', 'schedulings.work_assignment_id')
                ->leftJoin('earnings', 'earnings.id', '=', 'schedulings.earning_id')
                ->leftJoin('employments', 'employees.id', '=', 'employments.employee_id')
                ->where('employments.department_id', '=', $department)
                ->groupBy("employees.id")
                ->get();
            }

            return datatables()->of(
                $employee_schedule
            )
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function copy_schedule(Request $request) {
        if($request->data['department'] === "all") {
            $schedulings = Schedulings::selectRaw('DAYOFWEEK(date) as no_day, date, employee_id, start_time, end_time, details, type, type_description, status, work_assignment_id, earning_id')->whereBetween('date', [$request->data['first'], $request->data['last']])->orderBy('no_day')->get();
        }
        else {
            $schedulings = Schedulings::selectRaw('DAYOFWEEK(date) as no_day, date, schedulings.employee_id, start_time, end_time, details, type, type_description, status, work_assignment_id, earning_id')
            ->leftJoin('employments', 'schedulings.employee_id', '=', 'employments.employee_id')
            ->where('employments.department_id', '=', $request->data['department'])
            ->whereBetween('date', [$request->data['first'], $request->data['last']])->orderBy('no_day')->get();
        }

        return response()->json(compact('schedulings'));
    }
    
    public function paste_schedule(Request $request) {
        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        Schedulings::create($request->all());
    }
}
