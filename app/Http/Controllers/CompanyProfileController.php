<?php

namespace App\Http\Controllers;

use App\Traits\GlobalFunction;
use Auth;
use App\ChartOfAccount;
use App\CompanyProfile;
use App\CompanyworkCalendar;
use Illuminate\Http\Request;

class CompanyProfileController extends Controller
{
    use GlobalFunction;

    public function index()
    {
        $record = ChartOfAccount::get();
        return view('backend.pages.payroll.transaction.company_profile.index', ["type"=>"2-view"], compact('record'));
    }

    public function store(Request $request) {
        
        $validate = $request->validate([
            'company_name' => 'required',
            'email' => 'required|unique:company_profiles|email',
            'legal_name' => 'required'
        ]);

        $request['company_no'] = $this->series('C', 'CompanyProfile');


        if($request->company_logo !== null) {
            // $last_id = CompanyProfile::latest('created_at')->count()===0?1:CompanyProfile::latest('created_at')->first()->id;
            $request['company_logo'] = $this->uploadFile($request->company_logo, 'images/payroll/company-profile/', date('Ymdhis'));
        }
        else {
            $request['company_logo'] = "default.png";
        }

        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;
        
        $company = CompanyProfile::create($request->all());
        $company_work_calendar_data = array(
            'workstation_id' => Auth::user()->workstation_id,
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id,
            'sunday_start_time' => $request->company_work_calendar['sunday_start_time'],
            'sunday_end_time' => $request->company_work_calendar['sunday_end_time'],
            'monday_start_time' => $request->company_work_calendar['monday_start_time'],
            'monday_end_time' => $request->company_work_calendar['monday_end_time'],
            'tuesday_start_time' => $request->company_work_calendar['tuesday_start_time'],
            'tuesday_end_time' => $request->company_work_calendar['tuesday_end_time'],
            'wednesday_start_time' => $request->company_work_calendar['wednesday_start_time'],
            'wednesday_end_time' => $request->company_work_calendar['wednesday_end_time'],
            'thursday_start_time' => $request->company_work_calendar['thursday_start_time'],
            'thursday_end_time' => $request->company_work_calendar['thursday_end_time'],
            'friday_start_time' => $request->company_work_calendar['friday_start_time'],
            'friday_end_time' => $request->company_work_calendar['friday_end_time'],
            'saturday_start_time' => $request->company_work_calendar['saturday_start_time'],
            'saturday_end_time' => $request->company_work_calendar['saturday_end_time']
        );
        CompanyworkCalendar::create($company_work_calendar_data);
        $last_record = array("id" => $company->id, "company_no" => $company->company_no);

        return response()->json(compact('validate', 'last_record'));
    }
    
    public function get()
    {
        if(request()->ajax()) {
            return datatables()->of(CompanyProfile::orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function edit($id)
    {
        $company = CompanyProfile::with('company_work_calendar')->where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('company'));
    }
    
    public function update(Request $request, $id)
    {
        if($request->company_logo !== null) {
            $request['company_logo'] = $this->uploadFile($request->company_logo, 'images/payroll/company-profile/', date('Ymdhis'));
        }
        else {
            $request['company_logo'] = "default.png";
        }
        CompanyProfile::find($id)->update($request->all());
        CompanyworkCalendar::where('workstation_id', $id)->update($request->company_work_calendar);

        return 'Successfully Updated!';
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            CompanyProfile::find($item)->delete();
            CompanyworkCalendar::where('workstation_id', $item)->delete();
        }
        
        return 'Record Deleted';
    }
    
}
