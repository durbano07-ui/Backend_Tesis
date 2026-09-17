<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LugarTrabajo extends Model
{
    protected $table = 'lugar_trabajo';
    public $timestamps = false;
    protected $fillable = ['nombre'];
}
