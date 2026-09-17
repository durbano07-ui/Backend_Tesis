<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdentificacionEtnica extends Model
{
    protected $table = 'identificacion_etnica';
    public $timestamps = false;
    protected $fillable = ['nombre'];
}
