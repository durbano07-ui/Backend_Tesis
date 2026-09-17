<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamenFisicoMedicina extends Model
{
    protected $table = 'examen_fisico_medicina';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'detalle_examen_fisico',
        'altura_x',
        'altura_y',
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
