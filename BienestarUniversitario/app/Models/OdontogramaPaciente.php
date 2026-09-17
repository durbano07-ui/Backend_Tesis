<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdontogramaPaciente extends Model
{
    protected $table = 'odontograma_paciente_odontologia';

    protected $fillable = ['id_usuario_paciente'];

    public function asignaciones()
    {
        return $this->hasMany(OdontogramaAsignacion::class, 'id_odontograma_paciente');
    }

    public function paciente()
    {
        return $this->belongsTo(User::class, 'id_usuario_paciente');
    }
}
