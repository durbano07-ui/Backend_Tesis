<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamenEstadoMentalPsicologia extends Model
{
    protected $table = 'examen_estado_mental_psicologia';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'apariencia',
        'actitud',
        'juicio',
        'sueno',
        'apetito',
        'afectividad',
        'orientacion',
        'atencion',
        'memoria',
        'lenguaje',
        'pensamiento',
        'conducta_motora',
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
