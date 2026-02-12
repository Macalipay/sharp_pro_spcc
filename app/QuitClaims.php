<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuitClaims extends Model
{
    protected $fillable = [
        'employee_id',
        'amount',
        'status',
        'date_released',
        'created_by',
        'updated_by',
    ];
    
    public function employee() {
        return $this->hasOne(EmployeeInformation::class, 'id', 'employee_id');
    }
}
