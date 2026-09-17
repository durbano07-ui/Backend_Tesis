<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SospechososConfirmadosInfluenza extends Model
{
    protected $table = 'sospechosos_confirmados_influenza';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'tipo',
        'detalle_resultados',
        'detalle_anticuerpos',
        'detalle_altamedica',
        'dias_aislamiento',
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
