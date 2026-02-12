<?php

namespace App\Http\Controllers;

use Auth;
use App\WithholdingTax;
use Illuminate\Http\Request;

class WithholdingTaxController extends Controller
{
    
    public function index()
    {
        return view('backend.pages.payroll.maintenance.withholding', ["type"=>"full-view"]);
    }

    public function get() {
        if(request()->ajax()) {
            return datatables()->of(WithholdingTax::orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'frequency' => ['required'],
            'range_from' => ['required'],
            'range_to' => ['required'],
            'fix_tax' => ['required'],
            'rate_on_excess' => ['required'],
        ]);
    
        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;
    
        WithholdingTax::create($request->all());
    }

    public function edit($id)
    {
        $withholding = WithholdingTax::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('withholding'));
    }

    public function update(Request $request, $id)
    {
        $request['updated_by'] = Auth::user()->id;
        WithholdingTax::find($id)->update($request->except('_token'));
        return "Record Saved";
    }
    
    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            WithholdingTax::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
}
