<?php
namespace App\Classes\Computation\Payroll;

use App\WithholdingTax as WithholdingTax_Benefits;

class WithholdingTax {
    public function getLastRange($end, $range) {
        $endrange = $end + $range;
        return $endrange;
    }

    public function getValue($salary, $frequency) {
        $withholding_tax = WithholdingTax_Benefits::whereRaw('? BETWEEN range_from and range_to', [$salary])->where('frequency', $frequency)->first();
        return $withholding_tax;
    }
}