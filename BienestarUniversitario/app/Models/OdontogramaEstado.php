<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdontogramaEstado extends Model
{
    protected $table = 'odontograma_estado_odontologia';

    protected $fillable = ['nombre', 'color', 'svg_icon', 'descripcion'];

    public function asignaciones()
    {
        return $this->hasMany(OdontogramaAsignacion::class, 'id_estado');
    }
}
