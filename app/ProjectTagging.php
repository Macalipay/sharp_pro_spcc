<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectTagging extends Model
{
    protected $fillable = [
        'employee_id',
        'project_id'
    ];
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
