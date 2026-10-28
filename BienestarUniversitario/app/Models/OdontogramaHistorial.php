<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Estado del odontograma de un paciente al cierre de un día de atención. */
class OdontogramaHistorial extends Model
{
    protected $table = 'odontograma_historial';

    protected $fillable = ['id_usuario_paciente', 'fecha', 'asignaciones'];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
        'asignaciones' => 'array',
    ];
}
