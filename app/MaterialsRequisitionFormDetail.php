<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialsRequisitionFormDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'materials_requisition_form_id',
        'quantity',
        'unit',
        'particulars',
        'location_to_be_used',
        'date_required',
        'approved_quantity',
        'remarks',
        'workstation_id',
        'created_by',
        'updated_by',
    ];

    public function header()
    {
        return $this->belongsTo(MaterialsRequisitionForm::class, 'materials_requisition_form_id', 'id');
    }
}

