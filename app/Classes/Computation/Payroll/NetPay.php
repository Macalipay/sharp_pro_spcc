<?php
namespace App\Classes\Computation\Payroll;

class NetPay {

    public function getValue($earnings, $deductions, $withholding_tax) {
        $amount = ($earnings - $deductions) - $withholding_tax;

        return $amount;
    }
}