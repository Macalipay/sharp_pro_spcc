<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccountingBillNote extends Model
{
    protected $fillable = [
        'accounting_bill_id',
        'note',
        'added_by',
        'added_at',
    ];

    public function bill()
    {
        return $this->belongsTo(AccountingBill::class, 'accounting_bill_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}

