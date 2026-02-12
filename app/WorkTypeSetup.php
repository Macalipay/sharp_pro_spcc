<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkTypeSetup extends Model
{
    protected $fillable = [
        'employee_id',
        'worktype_id',
        'days',
        'earnings',
        'hours',
        'remarks',
        'project_id'
    ];
    
    public function employee() {
        return $this->belongsTo(EmployeeInformation::class, 'employee_id');
    }
    
    public function worktype() {
        return $this->belongsTo(WorkType::class, 'worktype_id');
    }
    
    public function projects() {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
