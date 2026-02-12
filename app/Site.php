<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_name',
        'location',
        'person_in_charge',
        'workstation_id',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeInformation::class, 'person_in_charge');
    }
}
