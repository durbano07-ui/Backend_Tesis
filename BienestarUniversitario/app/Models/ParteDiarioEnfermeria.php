<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParteDiarioEnfermeria extends Model
{
    protected $table = 'parte_diario_enfermeria';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'fecha',
        'tipo_atencion',
        'tipo',
        'detalle_procedimiento',
        'detalle_medicacion',
        'id_procedimiento_enfermeria',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_doctor');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_paciente');
    }

    public function procedimiento(): BelongsTo
    {
        return $this->belongsTo(ProcedimientoEnfermeria::class, 'id_procedimiento_enfermeria');
    }
}
