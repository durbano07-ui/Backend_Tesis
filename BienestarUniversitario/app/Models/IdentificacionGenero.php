<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdentificacionGenero extends Model
{
    protected $table = 'identificacion_genero';
    public $timestamps = false;
    protected $fillable = ['nombre'];
}
