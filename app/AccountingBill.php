<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingBill extends Model
{
    use SoftDeletes;

    const STATUS_DRAFT = 'DRAFT';
    const STATUS_AWAITING_APPROVAL = 'AWAITING_APPROVAL';
    const STATUS_AWAITING_PAYMENT = 'AWAITING_PAYMENT';
    const STATUS_PAID = 'PAID';

    protected $fillable = [
        'bill_no',
        'supplier_id',
        'bill_date',
        'due_date',
        'description',
        'status',
        'total_amount',
        'accounts_payable_account_id',
        'payment_account_id',
        'recognition_journal_entry_id',
        'payment_journal_entry_id',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejected_reason',
        'paid_by',
        'paid_at',
        'payment_reference',
        'workstation_id',
        'created_by',
        'updated_by',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function items()
    {
        return $this->hasMany(AccountingBillItem::class, 'accounting_bill_id');
    }

    public function payable_account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'accounts_payable_account_id');
    }

    public function payment_account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'payment_account_id');
    }

    public function created_user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approved_user()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function paid_user()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function payments()
    {
        return $this->hasMany(AccountingBillPayment::class, 'accounting_bill_id');
    }

    public function notes()
    {
        return $this->hasMany(AccountingBillNote::class, 'accounting_bill_id');
    }

    public function histories()
    {
        return $this->hasMany(AccountingBillHistory::class, 'accounting_bill_id');
    }
}
