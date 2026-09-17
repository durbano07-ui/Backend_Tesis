<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListaVulnerabilidadesMedicoocupacional extends Model
{
    protected $table = 'lista_vulnerabilidades_medico';

    protected $fillable = [
        'id_usuario_doctor',
        'detalle_vulnerabilidad',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_doctor');
    }
}
