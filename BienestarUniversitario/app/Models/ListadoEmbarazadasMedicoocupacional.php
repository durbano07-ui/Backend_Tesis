<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListadoEmbarazadasMedicoocupacional extends Model
{
    protected $table = 'listado_embarazadas_medico';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'detalle_semanas_gestacion',
        'fecha_fum',
        'fecha_probable_parto',
        'numero_controles',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_doctor');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_paciente');
    }
}
