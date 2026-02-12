<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Materials extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'item_name',
        'item_code',
        'category',
        'brand',
        'unit_of_measure',
        'workstation_id',
        'created_by',
        'updated_by'
    ];

    public function units(){
        return $this->hasMany(MaterialUnit::class, 'material_id');
    }

}
