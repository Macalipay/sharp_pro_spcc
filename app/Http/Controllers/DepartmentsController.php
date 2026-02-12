<?php

namespace App\Http\Controllers;

use App\Departments;
use Auth;
use Illuminate\Http\Request;

class DepartmentsController extends Controller
{
    
    public function index()
    {
        $departments = Departments::orderBy('code', 'asc')->get();
        return view('backend.pages.payroll.maintenance.department', compact('departments'), ["type"=>"full-view"]);
    }

    public function get() {
        if(request()->ajax()) {
            return datatables()->of(Departments::orderBy('code', 'asc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'description' => ['required'],
            'code' => ['required'],

        ]);
        
        if (!Departments::where('description', $validatedData['description'])->exists()) {
            
            $request['workstation_id'] = Auth::user()->workstation_id;
            $request['created_by'] = Auth::user()->id;
            $request['updated_by'] = Auth::user()->id;
        
            Departments::create($request->all());
        }
        else {
            return false;
        }
    }

    public function edit($id)
    {
        $departments = Departments::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('departments'));
    }

    public function update(Request $request, $id)
    {
        $request['updated_by'] = Auth::user()->id;
        Departments::find($id)->update($request->all());
        return "Record Saved";
    }
    
    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            Departments::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
}
