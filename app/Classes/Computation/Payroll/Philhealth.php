<?php
namespace App\Classes\Computation\Payroll;

class Philhealth {
    public function getValue($salary) {
        $philhealth = $salary * 0.045;
        return $philhealth;
    }

}