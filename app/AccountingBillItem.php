<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingBillItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'accounting_bill_id',
        'chart_of_account_id',
        'description',
        'quantity',
        'unit_price',
        'line_total',
        'workstation_id',
        'created_by',
        'updated_by',
    ];

    public function bill()
    {
        return $this->belongsTo(AccountingBill::class, 'accounting_bill_id');
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }
}

