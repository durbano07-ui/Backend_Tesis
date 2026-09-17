<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrupoFuncionariosDiscapacidadMedicoocupacional extends Model
{
    protected $table = 'funcionarios_discapacidad_medico';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'id_tipo_discapacidad',
        'porcentaje_discapacidad',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_doctor');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_paciente');
    }

    public function tipoDiscapacidad(): BelongsTo
    {
        return $this->belongsTo(ListadoTiposDiscapacidadesMedicoocupacional::class, 'id_tipo_discapacidad', 'id');
    }
}
