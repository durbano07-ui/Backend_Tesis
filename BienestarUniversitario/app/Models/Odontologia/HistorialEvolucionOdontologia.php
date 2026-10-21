<?php

namespace App\Models\Odontologia;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialEvolucionOdontologia extends Model
{
    use \App\Models\Concerns\EncryptsHealthData;

    /** OWASP ASVS V6.1.2: datos de salud cifrados en reposo */
    protected array $encryptedHealthFields = ['detalle_tratamiento', 'detalle_procedimiento'];

    protected $table = 'historial_evolucion_odontologia';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'fecha',
        'detalle_tratamiento',
        'detalle_procedimiento',
        'prescripción_farmaceutica',
        'tipo_atencion',
    ];

    protected $casts = [
        'fecha' => 'date',
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
