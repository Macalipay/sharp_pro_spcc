<?php

namespace App\Http\Controllers;

use Auth;
use App\WorkAssignments;
use Illuminate\Http\Request;

class WorkAssignmentsController extends Controller
{
    public function index() 
    {
        return view('backend.pages.setup.payroll_setup.work_assignments', ["type"=>"full-view"]);
    }
    
    public function get() {
        if(request()->ajax()) {
            return datatables()->of(WorkAssignments::orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required',
            'status' => 'required',
        ]);
        
        if (!WorkAssignments::where('title', $validatedData['title'])->exists()) {
            
            $request['workstation_id'] = Auth::user()->workstation_id;
            $request['created_by'] = Auth::user()->id;
            $request['updated_by'] = Auth::user()->id;
        
            WorkAssignments::create($request->all());
        }
        else {
            return false;
        }
    }
    
    public function edit($id)
    {
        $work_assignment = WorkAssignments::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('work_assignment'));
    }

    public function update(Request $request, $id)
    {
        $request['updated_by'] = Auth::user()->id;
        WorkAssignments::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            WorkAssignments::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
}
