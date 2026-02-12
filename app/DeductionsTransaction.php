<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeductionsTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'sequence_no',
        'deduction_id',
        'rate',
        'hours',
        'total',
        'status',
        'workstation_id',
        'created_by',
        'updated_by'
    ];

    public function deduction() {
        return $this->hasOne(Deductions::class, 'id', 'deduction_id');
    }
}
