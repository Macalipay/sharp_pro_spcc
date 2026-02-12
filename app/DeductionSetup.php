<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeductionSetup extends Model
{
    protected $fillable = [
        'employee_id',
        'deduction_id',
        'amount',
        'sequence_no',
        'date'
    ];
    
    public function deductions()
    {
        return $this->belongsTo(Deduction::class, 'deduction_id');
    }
}
