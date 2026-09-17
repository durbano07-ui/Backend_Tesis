<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipoExamenMedicoocupacional extends Model
{
    protected $table = 'tipo_examen_medicoocupacional';

    protected $fillable = [
        'id_usuario_doctor',
        'id_grupo_tipo_examen',
        'detalle_tipo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_doctor');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoTipoExamenMedicoocupacional::class, 'id_grupo_tipo_examen');
    }
}
