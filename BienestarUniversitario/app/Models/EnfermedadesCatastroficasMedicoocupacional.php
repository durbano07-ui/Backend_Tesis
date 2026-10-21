<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnfermedadesCatastroficasMedicoocupacional extends Model
{
    use \App\Models\Concerns\EncryptsHealthData;

    /** OWASP ASVS V6.1.2: datos de salud cifrados en reposo */
    protected array $encryptedHealthFields = ['detalle_enfermedad', 'detalle_novedades'];

    protected $table = 'enfermedades_catastroficas_medico';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'detalle_enfermedad',
        'detalle_novedades',
        'clasificacion',
        'fecha_diagnostico',
        'cargo',
        'recibio_tratamiento',
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
