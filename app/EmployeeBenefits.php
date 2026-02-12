<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeBenefits extends Model
{
    use SoftDeletes;
    protected $table = 'employee_benefits';
    protected $fillable = [
        'benefits_id',
        'employee_id',
        'amount',
        'type',
        'created_by',
        'updated_by'
    ];
    
    public function benefits() {
        return $this->hasOne(Benefits::class, 'id', 'benefits_id');
    }

    public function employee() {
        return $this->hasOne(EmployeeInformation::class, 'id', 'employee_id');
    }

    public function user() {
        return $this->hasOne(User::class, 'id', 'created_by');
    }
}
