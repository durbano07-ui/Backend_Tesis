<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdontogramaAsignacion extends Model
{
    protected $table = 'odontograma_asignacion_odontologia';

    protected $fillable = [
        'id_odontograma_paciente',
        'id_numero_pieza',
        'id_numero_carilla',
        'id_estado',
        'fecha'
    ];

    public function odontogramaPaciente()
    {
        return $this->belongsTo(OdontogramaPaciente::class, 'id_odontograma_paciente');
    }

    public function pieza()
    {
        return $this->belongsTo(OdontogramaOdontologia::class, 'id_numero_pieza');
    }

    public function carilla()
    {
        return $this->belongsTo(OdontogramaPiezaCarilla::class, 'id_numero_carilla');
    }

    public function estado()
    {
        return $this->belongsTo(OdontogramaEstado::class, 'id_estado');
    }
}
