<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeDeduction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'deduction_id',
        'reference_name',
        'description',
        'total_amount',
        'payment_terms',
        'deduction_per_payroll',
        'deduction_frequency',
        'effective_start_payroll',
        'end_date',
        'auto_deduct_in_payroll',
        'stop_when_fully_paid',
        'allow_manual_override',
        'status',
        'total_paid',
        'remaining_balance',
        'workstation_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'total_amount' => 'float',
        'payment_terms' => 'integer',
        'deduction_per_payroll' => 'float',
        'auto_deduct_in_payroll' => 'boolean',
        'stop_when_fully_paid' => 'boolean',
        'allow_manual_override' => 'boolean',
        'total_paid' => 'float',
        'remaining_balance' => 'float',
        'effective_start_payroll' => 'date',
        'end_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeInformation::class, 'employee_id');
    }

    public function deduction()
    {
        return $this->belongsTo(Deductions::class, 'deduction_id');
    }

    public function transactions()
    {
        return $this->hasMany(EmployeeDeductionTransaction::class, 'employee_deduction_id');
    }
}
