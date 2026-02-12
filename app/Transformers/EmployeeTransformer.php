<?php 
namespace App\Transformers;

use App\EmployeeInformation;
use League\Fractal\TransformerAbstract;

class EmployeeTransformer extends TransformerAbstract
{
    public function transform(EmployeeInformation $employee)
    {
        return [
            'id' => $employee->id,
            'emp_no' => $employee->employee_no
        ];
    }
}