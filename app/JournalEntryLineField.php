<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntryLineField extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'journal_entry_id',
        'chart_of_account_id',
        'data_type',
        'data_id',
        'tax_rate',
        'region',
        'description',
        'debit_amount',
        'credit_amount',
        'department_cost_center',
        'project_code',
        'workstation_id',
        'created_by',
        'approved_by',
        'updated_by',
    ];

    public function journal_entry() {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function chart_of_account() {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }
}
