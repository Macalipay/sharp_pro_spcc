<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'subject',
        'details',
        'module',
        'source_id',
        'link',
        'type',
        'role',
        'status',
        'workstation_id',
        'created_by',
        'updated_by',
    ];
}
