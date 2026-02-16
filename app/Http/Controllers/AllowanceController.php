<?php

namespace App\Http\Controllers;

use Auth;
use App\ChartOfAccount;
use App\Allowance;
use App\AllowanceTagging;
use Illuminate\Http\Request;

class AllowanceController extends Controller
{
    public function index()
    {
        $record = ChartOfAccount::orderBy('id', 'desc')->get();
        return view('backend.pages.setup.payroll_setup.allowance', compact('record'), ["type"=>"full-view"]);
    }
    
    public function get() {
        if(request()->ajax()) {
            return datatables()->of(Allowance::orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'amount' => 'required'
        ]);
        
        if (!Allowance::where('name', $validatedData['name'])->exists()) {
            
            $request['workstation_id'] = Auth::user()->workstation_id;
            $request['created_by'] = Auth::user()->id;
            $request['updated_by'] = Auth::user()->id;
        
            Allowance::create($request->all());
        }
        else {
            return false;
        }
    }
    
    public function edit($id)
    {
        $allowance = Allowance::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('allowance'));
    }

    public function update(Request $request, $id)
    {
        $request['updated_by'] = Auth::user()->id;
        Allowance::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            Allowance::find($item)->delete();
        }
        
        return 'Record Deleted';
    }

    public function addAllowanceTag(Request $request) {
        if($request->action === "add") {
            $tag = AllowanceTagging::where('employee_id', $request->employee_id)
                ->where('allowance_id', $request->allowance_id)
                ->first();

            if ($tag) {
                $newAmount = $request->has('amount') ? $request->amount : $tag->amount;
                $tag->update(['amount' => $newAmount]);
            } else {
                AllowanceTagging::create([
                    'employee_id' => $request->employee_id,
                    'allowance_id' => $request->allowance_id,
                    'amount' => $request->has('amount') ? $request->amount : 0,
                ]);
            }
        }
        else {
            if ($request->filled('id')) {
                AllowanceTagging::where('id', $request->id)->delete();
            } else {
                AllowanceTagging::where('employee_id', $request->employee_id)
                    ->where('allowance_id', $request->allowance_id)
                    ->delete();
            }
        }

        return response()->json(['success' => true]);
    }

    public function getAllowance(Request $request) {
        $allowance = AllowanceTagging::with('allowances')
            ->where('employee_id', $request->employee_id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(compact('allowance'));
    }
    
    public function getAmount($id) {
        $allowance = Allowance::where('id', $id)->first();

        return response()->json(compact('allowance'));
    }
}
