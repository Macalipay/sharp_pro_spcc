<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EarningSetup extends Model
{
    protected $fillable = [
        'employee_id',
        'earning_id'
    ];
    
    public function earning()
    {
        return $this->belongsTo(Earnings::class, 'earning_id');
    }
}
