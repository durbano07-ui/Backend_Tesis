<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenDeExamenOtrosMedicoocupacional extends Model
{
    protected $table = 'orden_de_examen_otros_medicoocupacional';

    protected $fillable = [
        'id_orden_examen',
        'detalle_otro_examen',
    ];

    public function ordenExamen(): BelongsTo
    {
        return $this->belongsTo(OrdenDeExamenMedicoocupacional::class, 'id_orden_examen');
    }
}
