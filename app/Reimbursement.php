<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reimbursement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'chart_id',
        'status',
        'workstation_id',
        'created_by',
        'updated_by'
    ];
    
    public function chart() {
        return $this->belongsTo(ChartOfAccount::class, 'chart_id');
    }
}
