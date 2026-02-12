<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deductions extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'multiplier',
        'type',
        'taxable',
        'status',
        'chart_id',
        'workstation_id',
        'created_by',
        'updated_by'
    ];
    
    public function chart() {
        return $this->belongsTo(ChartOfAccount::class, 'chart_id');
    }
}
