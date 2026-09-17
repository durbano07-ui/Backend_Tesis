<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrupoVulnerableMedicoocupacional extends Model
{
    protected $table = 'grupo_vulnerable_medico';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'id_lista_vulnerabilidad',
        'detalle_otra_enfermedad',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_doctor');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_paciente');
    }

    public function vulnerabilidad(): BelongsTo
    {
        return $this->belongsTo(ListaVulnerabilidadesMedicoocupacional::class, 'id_lista_vulnerabilidad', 'id');
    }
}
