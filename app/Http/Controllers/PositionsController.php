<?php

namespace App\Http\Controllers;

use App\Positions;
use Auth;
use Illuminate\Http\Request;

class PositionsController extends Controller
{
    
    public function index()
    {
        $positions = Positions::orderBy('id', 'desc')->get();
        return view('backend.pages.payroll.maintenance.position', compact('positions'), ["type"=>"full-view"]);
    }

    public function get() {
        if(request()->ajax()) {
            return datatables()->of(Positions::orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'description' => ['required'],
            'position_type' => ['required'],
        ]);

        if (!Positions::where('description', $validatedData['description'])->exists()) {
            
            $request['workstation_id'] = Auth::user()->workstation_id;
            $request['created_by'] = Auth::user()->id;
            $request['updated_by'] = Auth::user()->id;
        
            Positions::create($request->all());
        }
        else {
            return false;
        }
    }

    public function edit($id)
    {
        $positions = Positions::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('positions'));
    }
    
    public function update(Request $request, $id)
    {
        $request['updated_by'] = Auth::user()->id;
        Positions::find($id)->update($request->all());
        return "Record Saved";
    }
    
    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            Positions::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
}
