<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryInstructionTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'template_text',
        'description',
        'workstation_id',
        'created_by',
        'updated_by',
    ];
}
