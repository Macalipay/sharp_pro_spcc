<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class MaterialCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'description',
        'workstation_id',
        'created_by',
        'updated_by'
    ];
}
