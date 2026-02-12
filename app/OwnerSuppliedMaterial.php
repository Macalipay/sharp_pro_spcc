<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OwnerSuppliedMaterial extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'material_id',
        'project_id',
        'description',
        'total_count',
        'quantity_stock',
        'status',
        'deleted_at',
        'workstation_id',
        'created_by',
        'updated_by',
    ];

    public function material()
    {
        return $this->belongsTo(Materials::class, 'material_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

}
