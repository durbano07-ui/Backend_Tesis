<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SospechososConfirmadosInfluenza extends Model
{
    use \App\Models\Concerns\EncryptsHealthData;

    /** OWASP ASVS V6.1.2: datos de salud cifrados en reposo */
    protected array $encryptedHealthFields = ['detalle_resultados', 'detalle_anticuerpos', 'detalle_altamedica'];

    protected $table = 'sospechosos_confirmados_influenza';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'tipo',
        'detalle_resultados',
        'detalle_anticuerpos',
        'detalle_altamedica',
        'dias_aislamiento',
        'tipo_trabajador',
        'lugar_trabajo',
        'fecha_resultados',
        'pcr',
        'prueba_rapida',
        'alta_medica',
        'descartado',
    ];

    protected $casts = [
        'pcr' => 'boolean',
        'prueba_rapida' => 'boolean',
        'alta_medica' => 'boolean',
        'descartado' => 'boolean',
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
