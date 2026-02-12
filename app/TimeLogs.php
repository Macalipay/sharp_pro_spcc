<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeLogs extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'date',
        'time_in',
        'time_out',
        'break_in',
        'break_out',
        'ot_in',
        'ot_out',
        'total_hours',
        'break_hours',
        'ot_hours',
        'late_hours',
        'undertime',
        'type',
        'status',
        'schedule_status',
        'workstation_id',
        'log_type',
        'created_by',
        'updated_by',
    ];
    
    public function employee() {
        return $this->belongsTo(EmployeeInformation::class, 'employee_id');
    }

    public function image() {
        return $this->belongsTo(ImageUpload::class, 'employee_id');
    }
    
    public function project() {
        return $this->belongsTo(ProjectTagging::class, 'employee_id', 'employee_id');
    }
    
    public function workdetails() {
        return $this->hasMany(WorkDetails::class, 'time_logs_id', 'id');
    }
}
