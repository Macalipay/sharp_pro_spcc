<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkAssignments extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'workstation_id',
        'status',
        'created_by',
        'updated_by'
    ];
}
