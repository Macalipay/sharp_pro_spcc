<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Classes extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id',
        'description',
        'payment_schedule',
        'tax_applicable',
        'government_mandated_benefits',
        'other_company_benefits',
        'workstation_id',
        'created_by',
        'updated_by'
    ];

    public function classes()
    {
        return $this->belongsTo(Classes::class, 'description');
    }

}
