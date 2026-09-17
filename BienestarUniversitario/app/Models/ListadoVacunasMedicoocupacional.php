<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListadoVacunasMedicoocupacional extends Model
{
    protected $table = 'listado_vacunas_medicoocupacional';

    protected $fillable = [
        'id_usuario_doctor',
        'detalle_vacuna',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_doctor');
    }
}
