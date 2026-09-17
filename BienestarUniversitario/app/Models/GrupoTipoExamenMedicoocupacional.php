<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrupoTipoExamenMedicoocupacional extends Model
{
    protected $table = 'grupo_tipo_examen_medicoocupacional';

    protected $fillable = [
        'id_usuario_doctor',
        'detalle_grupo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_doctor');
    }
}
