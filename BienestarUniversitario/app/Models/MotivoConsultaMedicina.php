<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MotivoConsultaMedicina extends Model
{
    protected $table = 'motivo_consulta_medicina';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'detalle_motivo',
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
