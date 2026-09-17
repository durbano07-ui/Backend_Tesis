<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignoAlarmaRecetaMedicoocupacional extends Model
{
    protected $table = 'signo_alarma_receta_medicoocupacional';

    protected $fillable = [
        'id_receta',
        'detalle_alarma',
    ];

    public function receta(): BelongsTo
    {
        return $this->belongsTo(RecetaMedicoocupacional::class, 'id_receta');
    }
}
