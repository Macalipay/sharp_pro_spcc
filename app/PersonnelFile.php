<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonnelFile extends Model
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
        'workstation_id',
        'created_by',
        'updated_by'
    ];
}
