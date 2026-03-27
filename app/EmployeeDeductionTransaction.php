<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeDeductionTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_deduction_id',
        'employee_id',
        'summary_id',
        'sequence_no',
        'deduction_id',
        'payroll_period_start',
        'payroll_period_end',
        'processed_date',
        'reference_name',
        'scheduled_amount',
        'actual_deducted_amount',
        'running_balance',
        'source',
        'notes',
        'payroll_reference_no',
        'status',
        'workstation_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'scheduled_amount' => 'float',
        'actual_deducted_amount' => 'float',
        'running_balance' => 'float',
        'payroll_period_start' => 'date',
        'payroll_period_end' => 'date',
        'processed_date' => 'date',
    ];

    public function employeeDeduction()
    {
        return $this->belongsTo(EmployeeDeduction::class, 'employee_deduction_id');
    }

    public function deduction()
    {
        return $this->belongsTo(Deductions::class, 'deduction_id');
    }
}
