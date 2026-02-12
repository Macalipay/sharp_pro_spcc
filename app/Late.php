<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Late extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'status',
        'late',
        'remarks',
    ];
    
    public function employee() {
        return $this->belongsTo(EmployeeInformation::class, 'employee_id');
    }
}
