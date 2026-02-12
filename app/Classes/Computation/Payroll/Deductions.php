<?php
namespace App\Classes\Computation\Payroll;

use App\DeductionsTransaction;

class Deductions {

    public function getValue($sequence_no, $employee_id) {
        $deductions = DeductionsTransaction::with('deduction')->where('sequence_no', $sequence_no)->where('employee_id', $employee_id)->get();

        return $deductions->sum('total');
    }
    
    public function getTaxable($sequence_no, $employee_id) {
        $deductions = DeductionsTransaction::with('deduction')->where('sequence_no', $sequence_no)->where('employee_id', $employee_id)->get();

        return $deductions->where('deduction.taxable', 1)->sum('total');
    }

}