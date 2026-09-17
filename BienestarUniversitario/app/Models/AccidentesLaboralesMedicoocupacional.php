<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccidentesLaboralesMedicoocupacional extends Model
{
    protected $table = 'accidentes_laborales_medico';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'lugar_accidente',
        'fecha_accidente',
        'detalle_parte_lesionada',
        'tipo_incapacidad',
        'causas_directas',
        'agente_accidente',
        'fuente_accidente',
        'tipo_accidente',
        'dias_perdidos',
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
