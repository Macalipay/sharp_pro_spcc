<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ImageUpload extends Model
{
    protected $fillable = [
        'timelog_id',
        'type',
        'filename',
        'status'
    ];

    public function timelogs() {
        return $this->belongsTo(TimeLogs::class, 'timelog_id');
    }
}
