<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoDireccion extends Model
{
    protected $table = 'tipo_direccion';
    public $timestamps = false;
    protected $fillable = ['nombre'];
}
