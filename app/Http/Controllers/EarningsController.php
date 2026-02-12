<?php

namespace App\Http\Controllers;

use Auth;
use App\Earnings;
use App\ChartOfAccount;
use Illuminate\Http\Request;

class EarningsController extends Controller
{
    
    public function index()
    {
        $record = ChartOfAccount::orderBy('id', 'desc')->get();
        return view('backend.pages.setup.payroll_setup.earnings', compact('record'), ["type"=>"full-view"]);
    }
    
    public function get() {
        if(request()->ajax()) {
            return datatables()->of(Earnings::with('chart')->orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'code' => 'required|unique:earnings',
            'type' => 'required',
            'chart_id' => 'required',
            'status' => 'required',
        ]);
        
        if (!Earnings::where('name', $validatedData['name'])->exists()) {
            
            $request['workstation_id'] = Auth::user()->workstation_id;
            $request['created_by'] = Auth::user()->id;
            $request['updated_by'] = Auth::user()->id;
        
            Earnings::create($request->all());
        }
        else {
            return false;
        }
    }
    
    public function edit($id)
    {
        $earning = Earnings::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('earning'));
    }

    public function update(Request $request, $id)
    {
        $request['updated_by'] = Auth::user()->id;
        Earnings::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            Earnings::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
}
