<?php

namespace App\Http\Controllers;

use App\Traits\GlobalFunction;
use App\EmployeeInformation;
use App\CompanyworkCalendar;
use App\PayrollCalendar;
use App\WorkCalendar;
use App\EarningSetup;
use App\EmployeeEducationalBackground;
use App\EmployeeCertification;
use App\EmployeeTraining;
use App\EmployeeWorkHistory;
use Auth;
use App\Region;
use App\WorkType;
use App\ClearanceType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Classes;
use App\Departments;
use App\Positions;
use App\LeaveType;
use App\Earnings;
use App\Allowance;
use App\Project;
use App\Employment;

class EmployeeInformationController extends Controller
{
    use GlobalFunction;

    public function index()
    {
        $classes = Classes::get();
        $department = Departments::get();
        $position = Positions::get();
        $leave_type = LeaveType::get();
        $payroll_calendar = PayrollCalendar::get();
        $earning = Earnings::get();
        $allowance = Allowance::get();
        $project = Project::get();
        $region = Region::get();
        $worktype = WorkType::get();
        $clearance = ClearanceType::get();

        return view('backend.pages.payroll.transaction.employee_information.index', compact('classes', 'position', 'department', 'leave_type', 'payroll_calendar', 'earning', 'allowance', 'project', 'region', 'clearance', 'worktype'), ["type"=>"2-view"]);
    }

    public function getCV($id)
    {
        $employee = EmployeeInformation::where('id', $id)->with('employments_tab', 'employments_tab.classes', 'employments_tab.positions', 'employments_tab.departments', 'leave_tab', 'works_calendar', 'compensations')->orderBy('id', 'desc')->get();
        $education = EmployeeEducationalBackground::where('employee_id', $id)->get();
        $certification = EmployeeCertification::where('employee_id', $id)->get();
        $training = EmployeeTraining::where('employee_id', $id)->get();
        $work_history = EmployeeWorkHistory::where('employee_id', $id)->get();

        return response()->json(compact('employee', 'education','certification','training','work_history'));
    }

    public function store(Request $request)
    {
        
        $validate = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'gender' => 'required',
            'birthdate' => 'required',
            'citizenship' => 'required',
            'phone1' => 'required',
            'street_1' => 'required',
            'barangay_1' => 'required',
            'city_1' => 'required',
            'province_1' => 'required',
            'country_1' => 'required',
            'zip_1' => 'required',
            'email' => 'required|unique:employees|email',
            'classes_id' => 'required',
            'position_id' => 'required',
            'department' => 'required',
            'employment_date' => 'required',
            'payroll_calendar_id' => 'required',
            'employment_type' => 'required',
            'civil_status' => 'required',
            'employment_status' => 'required',
            'birthplace' => 'required',
        ]);

        $existingEmployee = EmployeeInformation::where([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'birthdate' => $request->birthdate,
        ])->first();

        if ($existingEmployee) {

            return false;
        }
        else {

            $currentYear = now()->year;

            $dateOfEmployment = $request->employment_date;
            $numericDate = Carbon::parse($dateOfEmployment)->format('Ymd');
            
            $department_code = Departments::where('id', $request->department_id)->pluck('code')->first();


            $request['employee_no'] =  $department_code. $currentYear. '-' .$numericDate;

            if($request->profile_img !== null) {
                $request['profile_img'] = $this->uploadFile($request->profile_img, 'images/payroll/employee-information/', date('Ymdhis'));
            }
            else {
                $request['profile_img'] = "default.png";
            }
    
            $request['workstation_id'] = Auth::user()->workstation_id;
            $request['created_by'] = Auth::user()->id;
            $request['updated_by'] = Auth::user()->id;

            $employee = EmployeeInformation::create($request->all());
            $company_work_calendar = CompanyworkCalendar::where('workstation_id', Auth::user()->workstation_id)->firstOrFail();

            $employment = array(
                'employee_id' => $employee->id,
                'classes_id' => $request->classes_id,
                'position_id' => $request->position_id,
                'department_id' => $request->department_id,
                'payroll_calendar_id' => $request->payroll_calendar_id,
                'tax_rate' => $request->tax_rate,
                'employment_date' => $request->employment_date,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            );

            $employee_earnings = array(
                'employee_id' => $employee->id,
                'earning_id' => array("1", "2"),
            );
            
            foreach ($employee_earnings['earning_id'] as $earning_id) {
                EarningSetup::create([
                    'employee_id' => $employee_earnings['employee_id'],
                    'earning_id' => $earning_id,
                ]);
            }

            Employment::create($employment);


            if($company_work_calendar != null) {
                $workcalendar = array(
                    'employee_id' => $employee->id,
                    'sunday_start_time' => $company_work_calendar->sunday_start_time,
                    'sunday_end_time' => $company_work_calendar->sunday_end_time,
                    'monday_start_time' => $company_work_calendar->monday_start_time,
                    'monday_end_time' => $company_work_calendar->monday_end_time,
                    'tuesday_start_time' => $company_work_calendar->tuesday_start_time,
                    'tuesday_end_time' => $company_work_calendar->tuesday_end_time,
                    'wednesday_start_time' => $company_work_calendar->wednesday_start_time,
                    'wednesday_end_time' => $company_work_calendar->wednesday_end_time,
                    'thursday_start_time' => $company_work_calendar->thursday_start_time,
                    'thursday_end_time' => $company_work_calendar->thursday_end_time,
                    'friday_start_time' => $company_work_calendar->friday_start_time,
                    'friday_end_time' => $company_work_calendar->friday_end_time,
                    'employee_id' => $employee->id,
                    'workstation_id' => Auth::user()->workstation_id,
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id,
                );

                WorkCalendar::create($workcalendar);
            } 

            $last_record = array("id" => $employee->id, "employee_no" => $employee->employee_no);

            return response()->json(compact('validate', 'last_record'));
            
        }
    }

    public function get()
    {
        if(request()->ajax()) {
            return datatables()->of(EmployeeInformation::select("id", "employee_no", DB::raw("CONCAT(employees.firstname,' ',employees.lastname) as full_name"), "email")->orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function getmasterlist()
    {
        if(request()->ajax()) {
            return datatables()->of(EmployeeInformation::with('employments_tab', 'employments_tab.classes', 'employments_tab.positions', 'employments_tab.departments', 'leave_tab', 'works_calendar', 'compensations')->orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function employee($id)
    {
        $employee = EmployeeInformation::where('id', $id)->firstOrFail();
        return response()->json(compact('employee'));
    }

    public function edit($id)
    {
        $employee = EmployeeInformation::with('employments_tab', 'works_calendar', 'compensations', 'earning')->where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('employee'));
    }

    public function update(Request $request, $id)
    {
        
        $validate = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'gender' => 'required',
            'birthdate' => 'required',
            'citizenship' => 'required',
            'phone1' => 'required',
            'street_1' => 'required',
            'barangay_1' => 'required',
            'city_1' => 'required',
            'province_1' => 'required',
            'country_1' => 'required',
            'zip_1' => 'required',
            'email' => 'required',
            'classes_id' => 'required',
            'position_id' => 'required',
            'department_id' => 'required',
            'employment_date' => 'required',
            'payroll_calendar_id' => 'required',
            'employment_type' => 'required',
            'civil_status' => 'required',
            'employment_status' => 'required',
            'birthplace' => 'required',
        ]);

        if($request->profile_img !== null && $request->profile_img !== '') {
            $request['profile_img'] = $this->uploadFile($request->profile_img, 'images/payroll/employee-information/', date('Ymdhis'));
        }
        else {
            $request['profile_img'] = EmployeeInformation::where('id', $id)->first()->profile_img;
        }

        EmployeeInformation::findOrFail($id)->update($request->except('created_by'));

        if(Employment::where('employee_id', $id)->count() !== 0){
            $employment = array(
                'employee_id' => $id,
                'classes_id' => $request->classes_id,
                'position_id' => $request->position_id,
                'department_id' => $request->department_id,
                'payroll_calendar_id' => $request->payroll_calendar_id,
                'tax_rate' => $request->tax_rate,
                'employment_date' => $request->employment_date,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            );
    
            Employment::where('employee_id', $id)->update($employment);
        }
        else {
            $employment = array(
                'employee_id' => $id,
                'classes_id' => $request->classes_id,
                'position_id' => $request->position_id,
                'department_id' => $request->department_id,
                'payroll_calendar_id' => $request->payroll_calendar_id,
                'tax_rate' => $request->tax_rate,
                'employment_date' => $request->employment_date,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            );
    
            Employment::create($employment);
        }

        return response()->json(compact('validate'));
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            EmployeeInformation::find($item)->delete();
        }
        
        return 'Record Deleted';
    }

    public function masterlist() {
        $canDownload = auth()->user()->can('download_Employees Masterlist');

        return view('backend.pages.masterlist.employee', ["type"=>"full-view"], compact('canDownload'));
    }

    public function positionValidate($id) {

        $positionType = Positions::where('id', $id)->pluck('position_type')->first();

        if ($positionType === 'SINGLE') {

            $exists = Employment::where('position_id', $id)->exists();
            return response()->json(['exists' => $exists]);
        }
        else {

        }
    }
}

