<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OvertimeRequest extends Model
{
    protected $fillable = [
        'employee_id',
        'ot_date',
        'start_time',
        'end_time',
        'total_hours',
        'reason',
        'status',
        'ot_type',
        'approved_by',
        'approved_at',
    ];
    
    public function employee() {
        return $this->belongsTo(EmployeeInformation::class, 'employee_id');
    }
}
