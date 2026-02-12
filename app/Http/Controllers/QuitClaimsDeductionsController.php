<?php

namespace App\Http\Controllers;

use Auth;
use App\QuitClaimsDeductions;
use Illuminate\Http\Request;

class QuitClaimsDeductionsController extends Controller
{
    public function save(Request $request) {
        
        $validatedData = $request->validate([
            'deduction_type_id' => ['required']
        ]);

        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        QuitClaimsDeductions::create($request->all());
    }
    
    public function get($id) {
        if(request()->ajax()) {
            return datatables()->of(QuitClaimsDeductions::with('employee', 'deductions')->where('employee_id', $id)->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function edit($id)
    {
        $quit_claims_deductions = QuitClaimsDeductions::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('quit_claims_deductions'));
    }
    
    public function update(Request $request, $id)
    {
        QuitClaimsDeductions::find($id)->update($request->all());
        return "Record Saved";
    }
    
    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            QuitClaimsDeductions::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
}
