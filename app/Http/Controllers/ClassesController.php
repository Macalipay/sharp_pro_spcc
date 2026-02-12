<?php

namespace App\Http\Controllers;

use App\Classes;
use Auth;
use Illuminate\Http\Request;

class ClassesController extends Controller
{

    public function index()
    {
        $classes = Classes::orderBy('id', 'desc')->get();
        return view('backend.pages.payroll.maintenance.classes', compact('classes'), ["type"=>"full-view"]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'description' => ['required']
        ]);
        
        if (!Classes::where('description', $validatedData['description'])->exists()) {
            
            $request['workstation_id'] = Auth::user()->workstation_id;
            $request['created_by'] = Auth::user()->id;
            $request['updated_by'] = Auth::user()->id;
        
            Classes::create($request->all());
        }
        else {
            return false;
           
        }
    }

    public function get() {
        if(request()->ajax()) {
            return datatables()->of(Classes::orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function edit($id)
    {
        $classes = Classes::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('classes'));
    }
    
    public function update(Request $request, $id)
    {
        $request['tax_applicable'] = isset($request['tax_applicable'])?1:0;
        $request['government_mandated_benefits'] = isset($request['government_mandated_benefits'])?1:0;
        $request['other_company_benefits'] = isset($request['other_company_benefits'])?1:0;
        
        Classes::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            Classes::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
}
