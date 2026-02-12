<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChartOfAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'account_number',
        'account_name',
        'account_type',
        'description',
        'normal_balance',
        'workstation_id',
        'created_by',
        'updated_by'
    ];


    public function account_type()
    {
        return $this->belongsTo(AccountType::class, 'account_type');
    }
}
