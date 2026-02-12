<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AllowanceTagging extends Model
{
    protected $fillable = [
        'employee_id',
        'allowance_id',
        'amount'
    ];
    
    public function allowances()
    {
        return $this->belongsTo(Allowance::class, 'allowance_id');
    }
}
