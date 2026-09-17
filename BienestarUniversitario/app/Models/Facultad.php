<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facultad extends Model
{
    protected $table = 'facultad';
    public $timestamps = false;
    protected $fillable = ['nombre'];

    public function carreras()
    {
        return $this->hasMany(Carrera::class, 'id_facultad');
    }
}
