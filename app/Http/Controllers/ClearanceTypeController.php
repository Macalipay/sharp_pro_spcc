<?php

namespace App\Http\Controllers;

use App\ClearanceType;
use Illuminate\Http\Request;

class ClearanceTypeController extends Controller
{
    public function index()
    {
        return view('backend.pages.payroll.maintenance.clearance', ["type"=>"full-view"]);
    }

    public function get() {
        if(request()->ajax()) {
            return datatables()->of(ClearanceType::orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required'],
            'description' => ['required']
        ]);

        
        if (!ClearanceType::where('name', $validatedData['name'])->exists()) {
            ClearanceType::create($request->all());
        }
        else { 
            return false;
           
        }
    }

    public function edit($id)
    {
        $clearance_types = ClearanceType::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('clearance_types'));
    }
    
    public function update(Request $request, $id)
    {
        ClearanceType::find($id)->update($request->all());
        return "Record Saved";
    }
    
    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            ClearanceType::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
}
