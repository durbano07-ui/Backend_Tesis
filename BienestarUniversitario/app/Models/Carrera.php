<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    protected $table = 'carrera';
    public $timestamps = false;
    protected $fillable = ['id_facultad', 'nombre'];

    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'id_facultad');
    }
}
