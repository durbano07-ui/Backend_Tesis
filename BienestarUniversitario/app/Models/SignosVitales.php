<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignosVitales extends Model
{
    protected $table = 'signos_vitales';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'id_usuario_medico_general',
        'fecha',
        'presion_arterial_diastolica',
        'presion_arterial_sistolica',
        'frecuencia_cardiaca',
        'frecuencia_respiratoria',
        'temperatura',
        'talla',
        'peso',
        'atendido',
    ];

    protected $casts = [
        'fecha' => 'date',
        'presion_arterial_diastolica' => 'decimal:2',
        'presion_arterial_sistolica' => 'decimal:2',
        'temperatura' => 'decimal:2',
        'talla' => 'decimal:2',
        'peso' => 'decimal:2',
        'atendido' => 'boolean',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_doctor');
    }

    public function medicoGeneral(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_medico_general');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_paciente');
    }
}
