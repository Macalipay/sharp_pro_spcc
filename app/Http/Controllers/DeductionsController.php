<?php

namespace App\Http\Controllers;

use Auth;
use App\Deductions;
use App\ChartOfAccount;
use Illuminate\Http\Request;

class DeductionsController extends Controller
{
    
    public function index()
    {
        $record = ChartOfAccount::orderBy('id', 'desc')->get();
        return view('backend.pages.setup.payroll_setup.deductions', compact('record'), ["type"=>"full-view"]);
    }
    
    public function get() {
        if(request()->ajax()) {
            return datatables()->of(Deductions::with('chart')->orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'code' => 'required|unique:deductions',
            'type' => 'required',
            'chart_id' => 'required',
            'status' => 'required',
        ]);
        
        if (!Deductions::where('name', $validatedData['name'])->exists()) {
            
            $request['workstation_id'] = Auth::user()->workstation_id;
            $request['created_by'] = Auth::user()->id;
            $request['updated_by'] = Auth::user()->id;
        
            Deductions::create($request->all());
        }
        else {
            return false;
        }
    }
    
    public function edit($id)
    {
        $deductions = Deductions::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('deductions'));
    }

    public function update(Request $request, $id)
    {
        $request['updated_by'] = Auth::user()->id;
        Deductions::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            Deductions::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
}
