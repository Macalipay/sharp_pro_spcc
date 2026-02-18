<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingBillPayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'accounting_bill_id',
        'amount',
        'payment_date',
        'payment_account_id',
        'payment_reference',
        'journal_entry_id',
        'created_by',
        'updated_by',
    ];

    public function bill()
    {
        return $this->belongsTo(AccountingBill::class, 'accounting_bill_id');
    }

    public function payment_account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'payment_account_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

