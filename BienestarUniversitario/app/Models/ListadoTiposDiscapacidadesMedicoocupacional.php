<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListadoTiposDiscapacidadesMedicoocupacional extends Model
{
    protected $table = 'listado_discapacidades_medico';

    protected $fillable = [
        'id_usuario_doctor',
        'detalle_tipo',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_doctor');
    }
}
