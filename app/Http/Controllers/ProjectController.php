<?php

namespace App\Http\Controllers;

use Auth;
use App\Project;
use App\ProjectSplit;
use App\Region;
use App\ProjectTagging;
use App\ChartOfAccount;
use App\EmployeeInformation;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $region = Region::get();
        $employee = EmployeeInformation::get();
        return view('backend.pages.payroll.maintenance.project', compact('region', 'employee'), ["type"=>"full-view"]);
    }

    public function get() {
        if(request()->ajax()) {
            return datatables()->of(Project::orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'project_name' => ['required'],
            'region_id' => ['required'],
            'province_id' => ['required'],
            'city_id' => ['required'],
            'barangay_id' => ['required'],
            'postal_code' => ['required'],
            'project_code' => ['required'],
            'project_owner' => ['required'],
            'start_date' => ['required'],
            'completion_date' => ['required'],
            'project_completion' => ['required'],
            'project_architect' => ['required'],
            'project_consultant' => ['required'],
            'project_in_charge' => ['required'],
            'contract_price' => ['required'],
        ]);

        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;
    
        Project::create($request->all());
    }

    public function edit($id)
    {
        $project = Project::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('project'));
    }
    
    public function update(Request $request, $id)
    {
        Project::find($id)->update($request->all());
        return "Record Saved";
    }
    
    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            Project::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
    
    public function getRecord()
    {
        $project = Project::get();
        $chart = ChartOfAccount::get();
        return response()->json(compact('project', 'chart'));
    }

    public function split(Request $request) {
        $count = ProjectSplit::where('po_id', $request->po_id)->count();

        if($count === 0) {
            foreach ($request->data as $item) {
                ProjectSplit::create($item);
            }
        }
        else {
            ProjectSplit::where('po_id', $request->po_id)->delete();
            foreach ($request->data as $item) {
                ProjectSplit::create($item);
            }
        }
    }
    
    public function getSplit($id) {
        $record = ProjectSplit::where('po_id', $id)->get();
        return response()->json(compact('record'));
    }
    
    public function addProjectTag(Request $request) {
        if($request->action === "add") {
            ProjectTagging::create($request->all());
        }
        else {
            ProjectTagging::where('employee_id', $request->employee_id)->where('project_id', $request->project_id)->delete();
        }
    }

    public function getProject(Request $request) {
        $project = ProjectTagging::with('project')->where('employee_id', $request->employee_id)->get();

        return response()->json(compact('project'));
    }
    
    public function getEmployeeTag(Request $request) {
        $project = ProjectTagging::with('project')->where('project_id', $request->project_id)->get();

        return response()->json(compact('project'));
    }
}
