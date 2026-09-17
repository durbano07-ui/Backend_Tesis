<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatosAutopercepcionCiudadana extends Model
{
    protected $table = 'datos_autopercepcion_ciudadana';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_identificacion_etnica',
        'id_genero',
        'id_estado_civil',
        'nacionalidad',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function identificacionEtnica(): BelongsTo
    {
        return $this->belongsTo(IdentificacionEtnica::class, 'id_identificacion_etnica');
    }

    public function genero(): BelongsTo
    {
        return $this->belongsTo(IdentificacionGenero::class, 'id_genero');
    }

    public function estadoCivil(): BelongsTo
    {
        return $this->belongsTo(IdentificacionEstadoCivil::class, 'id_estado_civil');
    }
}
