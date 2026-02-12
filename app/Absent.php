<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Absent extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'status',
        'remarks'
    ];
    
    public function employee() {
        return $this->belongsTo(EmployeeInformation::class, 'employee_id');
    }
}
