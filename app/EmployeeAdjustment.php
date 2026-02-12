<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeAdjustment extends Model
{

    protected $table = 'employee_adjustments';

    protected $fillable = [
        'employee_id',
        'adjustment_type',
        'old_value',
        'new_value',
        'amount',
        'remarks',
        'effective_date',
        'adjusted_by',
        'status'
    ];

    protected $casts = [
        'effective_date' => 'date',
        'old_value' => 'decimal:2',
        'new_value' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    // Relationship to Employee
    public function employee()
    {
        return $this->belongsTo(Employment::class);
    }

    // Relationship to User/Admin who made the adjustment
    public function adjustedBy()
    {
        return $this->belongsTo(User::class, 'adjusted_by', 'id');
    }
}
