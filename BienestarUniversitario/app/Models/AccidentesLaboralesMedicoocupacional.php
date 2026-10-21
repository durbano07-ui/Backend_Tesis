<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccidentesLaboralesMedicoocupacional extends Model
{
    use \App\Models\Concerns\EncryptsHealthData;

    /** OWASP ASVS V6.1.2: datos de salud cifrados en reposo */
    protected array $encryptedHealthFields = ['diagnostico', 'detalle_parte_lesionada', 'causas_directas', 'novedades'];

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
        'cargo',
        'diagnostico',
        'gravedad',
        'novedades',
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
