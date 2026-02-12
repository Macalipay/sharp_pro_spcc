<?php

namespace App\Http\Controllers;

use Auth;
use App\HolidayType;
use Illuminate\Http\Request;

class HolidayTypeController extends Controller
{
    public function index()
    {
        return view('backend.pages.payroll.maintenance.holiday_type', ["type"=>"full-view"]);
    }
    
    public function get() {
        if(request()->ajax()) {
            return datatables()->of(HolidayType::orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'multiplier' => 'required'
        ]);
        
            
        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;
    
        HolidayType::create($request->all());
        return response()->json(compact('validatedData'));
    }
    
    public function edit($id)
    {
        $holiday_type = HolidayType::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('holiday_type'));
    }

    public function update(Request $request, $id)
    {
        $request['updated_by'] = Auth::user()->id;
        HolidayType::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            HolidayType::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
}
