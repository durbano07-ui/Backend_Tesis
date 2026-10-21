<?php

namespace App\Models\Odontologia;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MotivoConsultaOdontologia extends Model
{
    use \App\Models\Concerns\EncryptsHealthData;

    /** OWASP ASVS V6.1.2: datos de salud cifrados en reposo */
    protected array $encryptedHealthFields = ['detalle_motivo', 'detalle_tratamiento', 'detalle_medicamento'];

    protected $table = 'motivo_consulta_odontologia';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'detalle_motivo',
        'ultima_visita_fecha',
        'algun_tratamiento',
        'algun_medicamento',
        'detalle_tratamiento',
        'detalle_medicamento',
    ];

    protected $casts = [
        'ultima_visita_fecha' => 'date',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'id_usuario_doctor');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'id_usuario_paciente');
    }
}
