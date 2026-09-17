<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CitaMedica extends Model
{
    use HasFactory;

    protected $table = 'citas_medicas';

    protected $fillable = [
        'id_usuario_paciente',
        'id_usuario_doctor',
        'rol_doctor',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'estado',
        'motivo',
        'notas_doctor',
        'confirmada_por_paciente',
        'fecha_confirmacion',
    ];

    protected $casts = [
        'fecha' => 'date',
        'confirmada_por_paciente' => 'boolean',
        'fecha_confirmacion' => 'datetime',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_paciente');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_doctor');
    }
}
