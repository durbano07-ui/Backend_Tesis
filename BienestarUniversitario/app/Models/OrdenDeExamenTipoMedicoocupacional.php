<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenDeExamenTipoMedicoocupacional extends Model
{
    protected $table = 'orden_de_examen_tipo_medicoocupacional';

    protected $fillable = [
        'id_orden_examen',
        'id_tipo_examen',
    ];

    public function ordenExamen(): BelongsTo
    {
        return $this->belongsTo(OrdenDeExamenMedicoocupacional::class, 'id_orden_examen');
    }

    public function tipoExamen(): BelongsTo
    {
        return $this->belongsTo(TipoExamenMedicoocupacional::class, 'id_tipo_examen');
    }
}
