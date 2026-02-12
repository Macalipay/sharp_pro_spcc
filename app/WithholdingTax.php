<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WithholdingTax extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "frequency",
        "range_from",
        "range_to",
        "fix_tax",
        "rate_on_excess",
        "status",
        "workstation_id",
        "created_by",
        "updated_by"
    ];
}
