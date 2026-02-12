<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_name',
        'region_id',
        'province_id',
        'city_id',
        'barangay_id',
        'postal_code',
        'project_code',
        'workstation_id',
        'project_owner',
        'start_date',
        'completion_date',
        'project_completion',
        'project_architect',
        'project_consultant',
        'project_in_charge',
        'contract_price',
        'created_by',
        'updated_by',
        'address'
    ];
    
    protected $dates = ['deleted_at'];
    
    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id', 'region_id');
    }
    
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'province_id');
    }
    
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'city_id');
    }
    
    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }
}