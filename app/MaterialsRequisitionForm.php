<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialsRequisitionForm extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'date',
        'mrf_no',
        'project_id',
        'location',
        'requested_by',
        'noted_by',
        'approved_by',
        'workstation_id',
        'created_by',
        'updated_by',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(EmployeeInformation::class, 'requested_by', 'id');
    }

    public function notedBy()
    {
        return $this->belongsTo(EmployeeInformation::class, 'noted_by', 'id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(EmployeeInformation::class, 'approved_by', 'id');
    }

    public function details()
    {
        return $this->hasMany(MaterialsRequisitionFormDetail::class, 'materials_requisition_form_id', 'id');
    }
}
