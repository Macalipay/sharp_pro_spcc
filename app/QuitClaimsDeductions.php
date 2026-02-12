<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuitClaimsDeductions extends Model
{
    protected $fillable = [
        'employee_id',
        'deduction_type_id',
        'deduction_description',
        'deduction_amount',
        'deduction_remarks',
        'workstation_id',
        'created_by',
        'updated_by'
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeInformation::class, 'employee_id');
    }
    
    public function deductions()
    {
        return $this->belongsTo(Deductions::class, 'deduction_type_id');
    }
}
