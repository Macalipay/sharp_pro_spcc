<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccountingBillHistory extends Model
{
    protected $fillable = [
        'accounting_bill_id',
        'action',
        'description',
        'amount',
        'performed_by',
        'performed_at',
    ];

    public function bill()
    {
        return $this->belongsTo(AccountingBill::class, 'accounting_bill_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}

