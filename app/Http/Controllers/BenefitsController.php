<?php

namespace App\Http\Controllers;

use App\Benefits;
use App\EmployeeBenefits;
use App\ChartOfAccount;
use Auth;
use Illuminate\Http\Request;

class BenefitsController extends Controller
{
    public function index()
    {
        $benefits = Benefits::orderBy('id', 'desc')->get();
        $record = ChartOfAccount::orderBy('id', 'desc')->get();
        return view('backend.pages.payroll.maintenance.benefits', compact('benefits', 'record'), ["type"=>"full-view"]);
    }

    public function get() {
        if(request()->ajax()) {
            return datatables()->of(Benefits::with('chart')->orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    
    public function sss_summary($id) {
        if(request()->ajax()) {
            return datatables()->of(EmployeeBenefits::where('employee_id', $id)->where('benefits_id', 2)->with('benefits', 'employee', 'user')
                ->orderBy('id', 'desc')
                ->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function pagibig_summary($id) {
        if(request()->ajax()) {
            return datatables()->of(EmployeeBenefits::where('employee_id', $id)->where('benefits_id', 4)->with('benefits', 'employee', 'user')
                ->orderBy('id', 'desc')
                ->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function philhealth_summary($id) {
        if(request()->ajax()) {
            return datatables()->of(EmployeeBenefits::where('employee_id', $id)->where('benefits_id', 3)->with('benefits', 'employee', 'user')
                ->orderBy('id', 'desc')
                ->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'benefits' => ['required'],
            'description' => ['required'],
            'chart_id' => ['required']
        ]);

        
        if (!Benefits::where('benefits', $validatedData['benefits'])->exists()) {
            
            $request['workstation_id'] = Auth::user()->workstation_id;
            $request['account'] = '';
            $request['created_by'] = Auth::user()->id;
            $request['updated_by'] = Auth::user()->id;
        
            Benefits::create($request->all());
        }
        else { 
            return false;
           
        }
    }

    public function edit($id)
    {
        $benefits = Benefits::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('benefits'));
    }


    public function update(Request $request, $id)
    {
        Benefits::find($id)->update($request->all());
        return "Record Saved";
    }
    
    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            Benefits::find($item)->delete();
        }
        
        return 'Record Deleted';
    }

    public function governmentMandatedBenefits() 
    {
        $benefits = Benefits::where('type', 'government_mandated')->get();
        return response()->json(compact('benefits'));
    }

    public function otherCompanyBenefits() 
    {
        $benefits = Benefits::where('type', 'other')->get();
        return response()->json(compact('benefits'));
    }
}
