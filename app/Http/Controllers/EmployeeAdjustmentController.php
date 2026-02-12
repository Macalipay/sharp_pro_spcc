<?php

namespace App\Http\Controllers;

use App\EmployeeAdjustment;
use App\Employment;
use Illuminate\Http\Request;
use Auth;
class EmployeeAdjustmentController extends Controller
{
     public function index()
    {
        $adjustments = EmployeeAdjustment::get();
        $employments = Employment::with('employee_information')->get();
        return view('backend.pages.employee.adjustment.employee_adjustment', compact('adjustments', 'employments'), ["type"=>"full-view"]);
    }

    public function get() {
        if(request()->ajax()) {
            return datatables()->of(EmployeeAdjustment::with('employee.employee_information', 'adjustedBy')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

  public function store(Request $request)
    {
        // VALIDATE INPUT
        $validatedData = $request->validate([
            'employee_id'      => ['required'],
            'adjustment_type'  => ['required', 'in:SALARY,ALLOWANCES,DEDUCTION,POSITION,CORRECTION'],
            'old_value'        => ['nullable', 'numeric'],
            'new_value'        => ['nullable', 'numeric'],
            'amount'           => ['nullable', 'numeric'],
            'effective_date'   => ['required', 'date'],
            'remarks'          => ['nullable', 'string'],
            'status'           => ['required', 'in:PENDING,APPROVED,REJECTED'],
        ]);

        // ADD SYSTEM FIELDS
        $validatedData['adjusted_by'] = Auth::user()->id;
        $validatedData['workstation_id'] = Auth::user()->workstation_id;
        $validatedData['created_by'] = Auth::user()->id;
        $validatedData['updated_by'] = Auth::user()->id;

        // CREATE ADJUSTMENT
        $adjustment = EmployeeAdjustment::create($validatedData);

        return response()->json([
            'message' => 'Employee adjustment saved successfully.',
            'adjustment' => $adjustment
        ], 201);
    }

    public function edit($id)
    {
        $adjustments = EmployeeAdjustment::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('adjustments'));
    }

    public function update(Request $request, $id)
    {
        $request['updated_by'] = Auth::user()->id;
        EmployeeAdjustment::find($id)->update($request->all());
        return "Record Saved";
    }
    
    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            EmployeeAdjustment::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
}
