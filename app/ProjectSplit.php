<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectSplit extends Model
{
    protected $fillable = [
        'po_id',
        'project_id',
        'amount',
        'percentage'
    ];
    
    public function project() {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
