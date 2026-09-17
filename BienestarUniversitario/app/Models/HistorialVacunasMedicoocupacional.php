<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialVacunasMedicoocupacional extends Model
{
    protected $table = 'historial_vacunas_medicoocupacional';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'id_listado_vacuna',
        'dosis',
        'fecha',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_doctor');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_paciente');
    }

    public function vacuna(): BelongsTo
    {
        return $this->belongsTo(ListadoVacunasMedicoocupacional::class, 'id_listado_vacuna');
    }
}
