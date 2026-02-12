<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuitClaimsAdditions extends Model
{
    protected $fillable = [
        'employee_id',
        'earning_type_id',
        'description',
        'amount',
        'remarks',
        'workstation_id',
        'created_by',
        'updated_by'
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeInformation::class, 'employee_id');
    }
    
    public function earning()
    {
        return $this->belongsTo(Earnings::class, 'earning_type_id');
    }
}
