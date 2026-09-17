<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdentificacionEstadoCivil extends Model
{
    protected $table = 'identificacion_estado_civil';
    public $timestamps = false;
    protected $fillable = ['nombres'];
}
