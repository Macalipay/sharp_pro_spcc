<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CashAdvance extends Model
{
    protected $fillable = [
        'employee_id',
        'amount',
        'summary_id',
        'date',
        'purpose',
        'status',
        'reimbursement_date'
    ];

    protected $table = 'cash_advance';
}
