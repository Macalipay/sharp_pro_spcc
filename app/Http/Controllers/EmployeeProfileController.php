<?php

namespace App\Http\Controllers;

use Auth;
use App\Traits\GlobalFunction;
use App\EmployeeInformation;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use Carbon\Carbon;

use App\Classes;
use App\Departments;
use App\Positions;
use App\LeaveType;
use App\Earnings;
use App\EarningSetup;
use App\Allowance;
use App\Project;
use App\Region;
use App\WorkType;
use App\WorkCalendar;
use App\ClearanceType;
use App\CompanyworkCalendar;
use App\PayrollCalendar;
use App\Employment;
use App\EmployeeMovement;

class EmployeeProfileController extends Controller
{
    use GlobalFunction;

    public function index()
    {
        $classes = Classes::get();
        $department = Departments::get();
        $position = Positions::get();
        $leave_type = LeaveType::get();
        $earning = Earnings::get();
        $allowance = Allowance::get();
        $project = Project::get();
        $region = Region::get();
        $worktype = WorkType::get();
        $clearance = ClearanceType::get();
        $payroll_calendar = PayrollCalendar::get();

        return view('backend.pages.employee.index', compact('classes', 'position', 'department', 'leave_type', 'payroll_calendar', 'earning', 'allowance', 'project', 'region', 'clearance', 'worktype'), ["type"=>"full-view"]);
    }
    
    public function get()
    {
        if(request()->ajax()) {
            return datatables()->of(EmployeeInformation::with('employments_tab', 'employments_tab.classes', 'employments_tab.positions', 'employments_tab.departments', 'leave_tab', 'works_calendar', 'compensations')->orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function save(Request $request)
    {
        $request->merge([
            'phone1' => $this->normalizePhilippineMobileNumber($request->phone1),
            'phone2' => $this->normalizePhilippineMobileNumber($request->phone2),
            'emergency_no' => $this->normalizePhilippineMobileNumber($request->emergency_no),
            'tin_number' => $this->normalizeTinNumber($request->tin_number),
            'sss_number' => $this->normalizeGovernmentId($request->sss_number, [2, 7, 1]),
            'pagibig_number' => $this->normalizeGovernmentId($request->pagibig_number, [4, 4, 4]),
            'philhealth_number' => $this->normalizeGovernmentId($request->philhealth_number, [2, 9, 1]),
        ]);

        $validate = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'gender' => 'required',
            'birthdate' => 'required',
            'citizenship' => 'required',
            'phone1' => ['required', 'regex:/^\+639\d{9}$/'],
            'phone2' => ['nullable', 'regex:/^\+639\d{9}$/'],
            'emergency_no' => ['nullable', 'regex:/^\+639\d{9}$/'],
            'tin_number' => ['nullable', 'regex:/^\d{3}-\d{3}-\d{3}(-\d{3})?$/'],
            'sss_number' => ['nullable', 'regex:/^\d{2}-\d{7}-\d$/'],
            'pagibig_number' => ['nullable', 'regex:/^\d{4}-\d{4}-\d{4}$/'],
            'philhealth_number' => ['nullable', 'regex:/^\d{2}-\d{9}-\d$/'],
            'street_1' => 'required',
            'barangay_1' => 'required',
            'city_1' => 'required',
            'province_1' => 'required',
            'country_1' => 'required',
            'zip_1' => 'required',
            'email' => 'required|unique:employees|email',
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

        if($request->employment_status === "PROBATIONARY" && $request->employment_status === "TEMPORARY" && $request->employment_status === "REGULAR") {
            $request->validate([
                'hire_date' => 'required',
            ]);
        }

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
    
    public function edit($id)
    {
        $employee = EmployeeInformation::with('employments_tab', 'works_calendar', 'compensations', 'earning')->where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('employee'));
    }
    
    public function update(Request $request, $id)
    {
        $request->merge([
            'phone1' => $this->normalizePhilippineMobileNumber($request->phone1),
            'phone2' => $this->normalizePhilippineMobileNumber($request->phone2),
            'emergency_no' => $this->normalizePhilippineMobileNumber($request->emergency_no),
            'tin_number' => $this->normalizeTinNumber($request->tin_number),
            'sss_number' => $this->normalizeGovernmentId($request->sss_number, [2, 7, 1]),
            'pagibig_number' => $this->normalizeGovernmentId($request->pagibig_number, [4, 4, 4]),
            'philhealth_number' => $this->normalizeGovernmentId($request->philhealth_number, [2, 9, 1]),
        ]);
        
        $validate = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'gender' => 'required',
            'birthdate' => 'required',
            'citizenship' => 'required',
            'phone1' => ['required', 'regex:/^\+639\d{9}$/'],
            'phone2' => ['nullable', 'regex:/^\+639\d{9}$/'],
            'emergency_no' => ['nullable', 'regex:/^\+639\d{9}$/'],
            'tin_number' => ['nullable', 'regex:/^\d{3}-\d{3}-\d{3}(-\d{3})?$/'],
            'sss_number' => ['nullable', 'regex:/^\d{2}-\d{7}-\d$/'],
            'pagibig_number' => ['nullable', 'regex:/^\d{4}-\d{4}-\d{4}$/'],
            'philhealth_number' => ['nullable', 'regex:/^\d{2}-\d{9}-\d$/'],
            'street_1' => 'required',
            'barangay_1' => 'required',
            'city_1' => 'required',
            'province_1' => 'required',
            'country_1' => 'required',
            'zip_1' => 'required',
            'email' => "required|email|unique:employees,email,{$id}",
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

        $employee = EmployeeInformation::where('id', $id)->first();


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

    public function convert_employment_status() {
        $employees = EmployeeInformation::with('employments_tab')->where('employment_status', 'PROBATIONARY')->get();
        $data = [];

        foreach($employees as $employee) {
            $date = Carbon::now();
            $hire = Carbon::parse($employee->employments_tab->employment_date);

            $interval = $date->diff($hire);

            $years = $interval->y;
            $months = $interval->m;
            
            $totalMonths = ($years * 12) + $months;

            if($totalMonths >= 6) {
                EmployeeInformation::where('id', $employee->id)->update(["employment_status" => "REGULAR"]);
            }
        }
        return response()->json(compact('data'));
    }

    public function auditTrail($id)
    {
        $sources = [
            ['table' => 'employees', 'key' => 'id', 'label' => 'Basic Information', 'fields' => [
                'firstname' => 'First Name',
                'middlename' => 'Middle Name',
                'lastname' => 'Last Name',
                'gender' => 'Gender',
                'civil_status' => 'Civil Status',
                'employment_status' => 'Employment Status',
                'employment_type' => 'Payout Schedule',
                'email' => 'Email',
                'phone1' => 'Phone',
            ]],
            ['table' => 'employments', 'key' => 'employee_id', 'label' => 'Employment Details', 'fields' => [
                'classes_id' => 'Class',
                'position_id' => 'Position',
                'department_id' => 'Department',
                'payroll_calendar_id' => 'Payroll Calendar',
                'employment_date' => 'Employment Date',
                'tax_rate' => 'Tax Rate',
            ]],
            ['table' => 'work_calendars', 'key' => 'employee_id', 'label' => 'Work Calendar', 'fields' => [
                'monday_start_time' => 'Monday Start',
                'monday_end_time' => 'Monday End',
                'tuesday_start_time' => 'Tuesday Start',
                'tuesday_end_time' => 'Tuesday End',
                'wednesday_start_time' => 'Wednesday Start',
                'wednesday_end_time' => 'Wednesday End',
                'thursday_start_time' => 'Thursday Start',
                'thursday_end_time' => 'Thursday End',
                'friday_start_time' => 'Friday Start',
                'friday_end_time' => 'Friday End',
            ]],
            ['table' => 'compensations', 'key' => 'employee_id', 'label' => 'Compensation Summary', 'fields' => [
                'monthly_salary' => 'Monthly Salary',
                'daily_salary' => 'Daily Salary',
                'hourly_salary' => 'Hourly Salary',
                'tax' => 'Tax',
                'sss' => 'SSS',
                'phic' => 'Philhealth',
                'pagibig' => 'Pag-ibig',
            ]],
            ['table' => 'employee_educational_backgrounds', 'key' => 'employee_id', 'label' => 'Educational Background', 'fields' => [
                'educational_attainment' => 'Attainment',
                'course' => 'Course',
                'school' => 'School',
                'school_year' => 'School Year',
            ]],
            ['table' => 'employee_work_histories', 'key' => 'employee_id', 'label' => 'Work History', 'fields' => [
                'company' => 'Company',
                'position' => 'Position',
                'date_hired' => 'Date Hired',
                'date_of_resignation' => 'Date Resigned',
            ]],
            ['table' => 'employee_certifications', 'key' => 'employee_id', 'label' => 'Certification', 'fields' => [
                'certification_no' => 'Certification No.',
                'certification_name' => 'Certification Name',
                'certification_authority' => 'Authority',
                'certification_status' => 'Status',
            ]],
            ['table' => 'employee_trainings', 'key' => 'employee_id', 'label' => 'Training', 'fields' => [
                'training_no' => 'Training No.',
                'training_name' => 'Training Name',
                'training_provider' => 'Provider',
                'training_date' => 'Training Date',
            ]],
            ['table' => 'employee_movements', 'key' => 'employee_id', 'label' => 'Movement', 'fields' => [
                'movement_type' => 'Movement Type',
                'prev_records' => 'Previous',
                'new_records' => 'New',
                'effective_date' => 'Effective Date',
            ]],
        ];

        $auditLogs = collect();

        foreach ($sources as $source) {
            $rows = DB::table($source['table'] . ' as t')
                ->leftJoin('users as updated_user', 'updated_user.id', '=', 't.updated_by')
                ->leftJoin('users as created_user', 'created_user.id', '=', 't.created_by')
                ->where('t.' . $source['key'], $id)
                ->select(
                    't.*',
                    't.created_at',
                    't.updated_at',
                    'updated_user.firstname as updated_firstname',
                    'updated_user.lastname as updated_lastname',
                    'created_user.firstname as created_firstname',
                    'created_user.lastname as created_lastname'
                )
                ->get();

            foreach ($rows as $row) {
                $timestamp = $row->updated_at ?: $row->created_at;

                if (!$timestamp) {
                    continue;
                }

                $updatedName = trim(($row->updated_firstname ?? '') . ' ' . ($row->updated_lastname ?? ''));
                $createdName = trim(($row->created_firstname ?? '') . ' ' . ($row->created_lastname ?? ''));
                $isUpdated = $row->updated_at && $row->created_at && $row->updated_at != $row->created_at;

                $auditLogs->push([
                    'user' => $updatedName !== '' ? $updatedName : ($createdName !== '' ? $createdName : 'System'),
                    'change_type' => ($isUpdated ? 'Updated ' : 'Created ') . $source['label'],
                    'description' => $this->buildAuditDescription($source, $row),
                    'timestamp' => Carbon::parse($timestamp)->format('Y-m-d H:i:s'),
                ]);
            }
        }

        $data = $auditLogs
            ->sortByDesc(function ($item) {
                return strtotime($item['timestamp']);
            })
            ->values();

        return response()->json(compact('data'));
    }

    private function buildAuditDescription(array $source, $row)
    {
        if ($source['table'] === 'employee_movements') {
            $from = $this->formatAuditValue($row->prev_records ?? null);
            $to = $this->formatAuditValue($row->new_records ?? null);
            return 'from "' . $from . '" to "' . $to . '"';
        }

        $parts = [];
        foreach ($source['fields'] as $field => $label) {
            $to = $this->formatAuditValue($row->{$field} ?? null);
            if ($to === '-') {
                continue;
            }
            $parts[] = $label . ': from "-" to "' . $to . '"';
        }

        if (empty($parts)) {
            return 'from "-" to "record saved"';
        }

        return implode('; ', $parts);
    }

    private function formatAuditValue($value)
    {
        if ($value === null) {
            return '-';
        }

        $formatted = trim((string) $value);
        return $formatted === '' ? '-' : $formatted;
    }

    private function normalizePhilippineMobileNumber($value)
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) $value);

        if ($digits === '') {
            return null;
        }

        if ($digits === '63') {
            return null;
        }

        if (strpos($digits, '63') === 0 && strlen($digits) === 12) {
            $localNumber = substr($digits, 2);
        } elseif (strpos($digits, '0') === 0 && strlen($digits) === 11) {
            $localNumber = substr($digits, 1);
        } elseif (strpos($digits, '9') === 0 && strlen($digits) === 10) {
            $localNumber = $digits;
        } else {
            return $value;
        }

        if (! preg_match('/^9\d{9}$/', $localNumber)) {
            return $value;
        }

        return '+63' . $localNumber;
    }

    private function normalizeGovernmentId($value, array $groups)
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) $value);

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) !== array_sum($groups)) {
            return $value;
        }

        $parts = [];
        $offset = 0;
        foreach ($groups as $group) {
            $parts[] = substr($digits, $offset, $group);
            $offset += $group;
        }

        return implode('-', $parts);
    }

    private function normalizeTinNumber($value)
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) $value);

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 9) {
            return $this->normalizeGovernmentId($digits, [3, 3, 3]);
        }

        if (strlen($digits) === 12) {
            return $this->normalizeGovernmentId($digits, [3, 3, 3, 3]);
        }

        return $value;
    }
}
