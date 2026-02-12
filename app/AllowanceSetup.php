<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AllowanceSetup extends Model
{
    protected $fillable = [
        'employee_id',
        'allowance_id',
        'amount',
        'sequence_no',
        'days',
        'total_amount',
        'date'
    ];
    
    public function allowances()
    {
        return $this->belongsTo(Allowance::class, 'allowance_id');
    }
}
