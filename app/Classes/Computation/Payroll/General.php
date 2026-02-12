<?php
namespace App\Classes\Computation\Payroll;

class General {
    public function deduction($sss, $pagibig, $philhealth) {
        $deduction = $sss + $pagibig + $philhealth;

        return $deduction;
    } 

    public function netPay($sss, $pagibig, $philhealth, $salary, $tax) {
        $netpay = ($salary - deduction($sss, $pagibig, $philhealth)) - $tax;
        return $netpay;
    }
}