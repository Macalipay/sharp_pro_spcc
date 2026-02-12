<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Positions extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id',
        'description',
        'position_type',
        'workstation_id',
        'created_by',
        'updated_by'
    ];

    public function positions()
    {
        return $this->belongsTo(Positions::class, 'position_id');
    }
}
