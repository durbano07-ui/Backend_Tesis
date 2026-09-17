<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrupoRiesgoPsicosocialMedicoocupacional extends Model
{
    protected $table = 'grupo_riesgo_psico_medico';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'detalle_diagnostico',
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
