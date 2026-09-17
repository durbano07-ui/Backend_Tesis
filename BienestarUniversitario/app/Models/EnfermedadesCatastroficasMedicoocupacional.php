<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnfermedadesCatastroficasMedicoocupacional extends Model
{
    protected $table = 'enfermedades_catastroficas_medico';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'detalle_enfermedad',
        'detalle_novedades',
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
