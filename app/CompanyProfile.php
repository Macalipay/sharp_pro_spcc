<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_no',
        'company_name',
        'email',
        'contact_number',
        'address',
        'legal_name',
        'tin',
        'company_logo',
        'theme',
        'color',
        'details',
        'dark_mode',
        'bank_account',
        'payroll_liability',
        'salary_expense',
        'salary_payable',
        'opening_balance_date',
        'created_by',
        'updated_by'
    ];
    
    public function company_work_calendar() {
        return $this->hasOne(CompanyworkCalendar::class, 'workstation_id', 'id');
    }
}
