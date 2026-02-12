<?php

namespace App\Http\Controllers;

use Auth;
use App\WorkType;
use Illuminate\Http\Request;

class WorkTypeController extends Controller
{
    public function index()
    {
        return view('backend.pages.payroll.maintenance.work_type', ["type"=>"full-view"]);
    }

    public function get() {
        if(request()->ajax()) {
            return datatables()->of(WorkType::orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required']
        ]);

        
        if (!WorkType::where('name', $validatedData['name'])->exists()) {
            WorkType::create($request->all());
        }
        else { 
            return false;
        }
    }

    public function edit($id)
    {
        $work_type = WorkType::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('work_type'));
    }
    
    public function update(Request $request, $id)
    {
        WorkType::find($id)->update($request->all());
        return "Record Saved";
    }
    
    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            WorkType::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
}
