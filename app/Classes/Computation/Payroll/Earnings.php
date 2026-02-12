<?php
namespace App\Classes\Computation\Payroll;

use App\EarningsTransaction;

class Earnings {

    public function getValue($sequence_no, $employee_id) {
        $earnings = EarningsTransaction::with('earning')->where('sequence_no', $sequence_no)->where('employee_id', $employee_id)->get();

        return $earnings->sum('total');
    }

    public function getTaxable($sequence_no, $employee_id) {
        $earnings = EarningsTransaction::with('earning')->where('sequence_no', $sequence_no)->where('employee_id', $employee_id)->get();

        return $earnings->where('earning.taxable', 1)->sum('total');
    }

}