<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'supplier_name',
        'contact_no',
        'contact_person',
        'address',
        'tin_no',
        'payment_terms',
        'bank_name',
        'bank_account',
        '2303',
        'business_registration',
        'sample_invoice',
        'nda',
        'vaf',
        'business_permit',
        'vatable',
        'workstation_id',
        'created_by',
        'updated_by'
    ];
}
