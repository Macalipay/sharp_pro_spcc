<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollSummaryNote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'summary_id',
        'note',
        'workstation_id',
        'created_by',
        'updated_by',
    ];
}

