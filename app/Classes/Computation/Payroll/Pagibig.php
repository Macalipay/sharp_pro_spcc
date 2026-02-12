<?php
namespace App\Classes\Computation\Payroll;

class Pagibig {
    public function getValue($salary) {
        if($salary > 1500) { 
            if($salary >= 5000) {
                $pagibig = 5000 * 0.02;
            }
            else {
                $pagibig = $salary * 0.02;
            }
        }
        else {
            $pagibig = $salary * 0.01;
        }

        return $pagibig;
    }

}