<?php

namespace App\Http\Controllers;

use Auth;
use App\Holiday;
use App\HolidayType;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index()
    {
        $record = HolidayType::get();
        return view('backend.pages.payroll.maintenance.holiday',compact('record'), ["type"=>"full-view"]);
    }
    
    public function get() {
        if(request()->ajax()) {
            return datatables()->of(Holiday::with('holiday_type')->orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'date' => 'required',
            'holiday_type_id' => 'required'
        ]);
        
            
        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;
    
        Holiday::create($request->all());
        return response()->json(compact('validatedData'));
    }
    
    public function edit($id)
    {
        $holiday = Holiday::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('holiday'));
    }

    public function update(Request $request, $id)
    {
        $request['updated_by'] = Auth::user()->id;
        Holiday::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            Holiday::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
}
