<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecomendacionNofarmacologicaRecetaMedicoocupacional extends Model
{
    protected $table = 'receta_recomendaciones_nofarmaco_medicoocupacional';

    protected $fillable = [
        'id_receta',
        'detalle_recomendacion',
    ];

    public function receta(): BelongsTo
    {
        return $this->belongsTo(RecetaMedicoocupacional::class, 'id_receta');
    }
}
