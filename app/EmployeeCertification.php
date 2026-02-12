<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeCertification extends Model
{
    protected $fillable = [
        'certification_no',
        'certification_name',
        'certification_authority',
        'certification_description',
        'certification_date',
        'certification_expiration_date',
        'certification_level',
        'certification_status',
        'certification_achievements',
        'certification_renewal_date',
        'recertification_date',
        'employee_id',
        'created_by',
        'updated_by'
    ];
}
