<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Holiday extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'date',
        'holiday_type_id',
        'workstation_id',
        'created_by',
        'updated_by'
    ];

    public function holiday_type()
    {
        return $this->belongsTo(HolidayType::class, 'holiday_type_id');
    }
}
