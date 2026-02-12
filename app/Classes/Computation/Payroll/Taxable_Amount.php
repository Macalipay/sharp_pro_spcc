<?php
namespace App\Classes\Computation\Payroll;

class Taxable_Amount {

    public function getValue($taxable, $tax_sensitive) {
        $amount = $taxable - $tax_sensitive;

        return $amount;
    }
}