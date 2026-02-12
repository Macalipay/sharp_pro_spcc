<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Benefits extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id',
        'benefits',
        'description',
        'account',
        'type',
        'chart_id',
        'workstation_id',
        'created_by',
        'updated_by'
    ];
    
    public function chart() {
        return $this->belongsTo(ChartOfAccount::class, 'chart_id');
    }
}
