<?php

namespace App\Http\Controllers;

use Auth;
use App\CashAdvance;
use Illuminate\Http\Request;

class CashAdvanceController extends Controller
{
    public function getCA($id, $emp_id) {
        $cash_advance = CashAdvance::where('summary_id', $id)->where('employee_id', $emp_id)->get();

        if(request()->ajax()) {
            return datatables()->of($cash_advance)
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function save(Request $request) {
        $ca_validate = $request->validate([
            'amount' => ['required'],
            'date' => ['required']
        ]);

        $cash_advance = CashAdvance::create($request->all());
    }
    
    public function edit($id)
    {
        $cash_advance = CashAdvance::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('cash_advance'));
    }

    public function update(Request $request, $id)
    {
        $request['updated_by'] = Auth::user()->id;
        CashAdvance::find($id)->update($request->all());
        return "Record Saved";
    }
    
    
    public function destroy(Request $request) {
        $cash_advance = CashAdvance::where('id', $request->id)->delete();
    }
}
