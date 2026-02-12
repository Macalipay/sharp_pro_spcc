<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeInformation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id',
        'employee_no',
        'firstname',
        'middlename',
        'lastname',
        'suffix',
        'birthdate',
        'gender',
        'citizenship',
        'phone1',
        'phone2',
        'street_1',
        'barangay_1',
        'city_1',
        'province_1',
        'country_1',
        'zip_1',
        'street_2',
        'barangay_2',
        'city_2',
        'province_2',
        'country_2',
        'zip_2',
        'profile_img',
        'bankaccount',
        'emergency_name',
        'emergency_no',
        'email',
        'password',
        'bank_account',
        'tin_number',
        'sss_number',
        'pagibig_number',
        'philhealth_number',
        'status',
        'employment_status',
        'rfid',
        'birthplace',
        'civil_status',
        'emergency_relationship',
        'bank_name',
        'bank_account_no',
        'employment_type',
        'workstation_id',
        'created_by',
        'updated_by'
    ];

    protected $table = "employees";

    public function employments_tab() {
        return $this->hasOne(Employment::class, 'employee_id', 'id');
    }
    
    public function earning() {
        return $this->hasOne(EarningSetup::class, 'employee_id', 'id');
    }
    
    public function leave_tab() {
        return $this->hasMany(Leaves::class, 'employee_id', 'id');
    }
    
    public function absents() {
        return $this->hasMany(Absent::class, 'employee_id', 'id');
    }

    public function absentsTimelogs() {
        return $this->hasMany(Absent::class, 'employee_id', 'id')->where('status', 0);;
    }

    public function absentsAdjustments() {
        return $this->hasMany(Absent::class, 'employee_id', 'id')->where('status', 1);
    }

    public function lates() {
        return $this->hasMany(Late::class, 'employee_id', 'id');
    }

    public function latesTimelogs() {
        return $this->hasMany(Late::class, 'employee_id', 'id')->where('status', 0);;
    }

    public function latesAdjustments() {
        return $this->hasMany(Late::class, 'employee_id', 'id')->where('status', 1);
    }

    public function works_calendar() {
        return $this->hasOne(WorkCalendar::class, 'employee_id', 'id');
    }

    public function compensations() {
        return $this->hasOne(Compensations::class, 'employee_id', 'id');
    }
    
    public function approval() {
        return $this->belongsTo(TimeLogApprovals::class, 'id', 'employee_id');
    }
    
    public function clearance() {
        return $this->belongsTo(Clearance::class, 'id', 'employee_id');
    }

    public function quit_claims() {
        return $this->belongsTo(QuitClaims::class, 'id', 'employee_id');
    }
    
    public function additionals() {
        return $this->hasMany(QuitClaimsAdditions::class, 'employee_id', 'id');
    }

    public function deductions() {
        return $this->hasMany(QuitClaimsDeductions::class, 'employee_id', 'id');
    }
    
    public function timelogs() {
        return $this->hasMany(TimeLogs::class, 'employee_id', 'id');
    }
    
    public function leaves() {
        return $this->hasMany(LeaveRequest::class, 'employee_id', 'id');
    }
}
