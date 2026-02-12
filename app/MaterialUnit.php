<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MaterialUnit extends Model
{
    protected $table = 'materials_unit';
    
    protected $fillable = ['material_id', 'unit_of_measure', 'description'];

}
