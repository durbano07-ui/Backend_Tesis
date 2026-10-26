<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ficha médica ocupacional emitida en una atención. "contenido" guarda los datos clínicos de la
 * ficha (motivo, examen, constantes, diagnósticos, aptitud...) cifrados; la identificación del
 * trabajador se toma siempre de su perfil en el sistema.
 */
class FichaMedicoocupacional extends Model
{
    protected $table = 'fichas_medicoocupacional';

    public const TIPOS = ['ingreso', 'periodico', 'retiro', 'reintegro', 'gestante', 'discapacidad'];

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'tipo',
        'fecha',
        'contenido',
    ];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
        'contenido' => 'encrypted:array',
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
