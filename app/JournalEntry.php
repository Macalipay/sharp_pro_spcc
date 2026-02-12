<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'entry_date',
        'reference_number',
        'description',
        'total_debit',
        'total_credit',
        'auto_reversing_date',
        'status',
        'approved_by',
        'workstation_id',
        'created_by',
        'updated_by',
    ];
}
