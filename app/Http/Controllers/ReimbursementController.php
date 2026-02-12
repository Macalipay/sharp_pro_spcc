<?php

namespace App\Http\Controllers;

use Auth;
use App\Reimbursement;
use App\ChartOfAccount;
use Illuminate\Http\Request;

class ReimbursementController extends Controller
{
    public function index()
    {
        $record = ChartOfAccount::orderBy('id', 'desc')->get();
        return view('backend.pages.payroll.maintenance.reimbursement', compact('record'), ["type"=>"full-view"]);
    }

    public function get() {
        if(request()->ajax()) {
            return datatables()->of(Reimbursement::with('chart')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'name' => ['required'],
            'chart_id' => ['required']
        ]);

        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        Reimbursement::create($request->all());

        return redirect()->back()->with('success','Successfully Added');
    }

    public function edit($id)
    {
        $reimbursement = Reimbursement::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('reimbursement'));
    }
    
    public function update(Request $request, $id)
    {
        Reimbursement::find($id)->update($request->all());
        return "Record Saved";
    }
    
    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            Reimbursement::find($item)->delete();
        }
        
        return 'Record Deleted';
    }

}
