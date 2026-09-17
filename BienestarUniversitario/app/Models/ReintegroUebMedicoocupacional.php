<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReintegroUebMedicoocupacional extends Model
{
    protected $table = 'reintegro_ueb_medicoocupacional';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'detalle_motivo_salida',
        'fecha_salida',
        'fecha_reintegro',
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
