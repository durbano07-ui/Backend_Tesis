<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialEvolucionPsicologia extends Model
{
    use \App\Models\Concerns\EncryptsHealthData;

    /** OWASP ASVS V6.1.2: datos de salud cifrados en reposo */
    protected array $encryptedHealthFields = ['detalle_evolucion'];

    protected $table = 'historial_evolucion_psicologia';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'fecha',
        'sesion_numero',
        'detalle_evolucion',
        'tipo_atencion',
    ];

    protected $casts = [
        'fecha' => 'date',
        'sesion_numero' => 'integer',
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
