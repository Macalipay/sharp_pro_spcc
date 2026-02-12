<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EarningsTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'sequence_no',
        'earning_id',
        'rate',
        'hours',
        'total',
        'status',
        'workstation_id',
        'created_by',
        'updated_by'
    ];

    public function earning() {
        return $this->hasOne(Earnings::class, 'id', 'earning_id');
    }
}
