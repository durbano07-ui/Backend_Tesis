<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FichaSocioeconomica extends Model
{
    protected $table = 'ficha_socioeconomica';

    protected $fillable = [
        'id_usuario',
        'nivel_instruccion_jefe_hogar',
        'empleo_jefe_hogar',
        'ingresos_mensuales',
        'tipo_vivienda',
        'numero_personas_hogar',
        'numero_aportantes',
        'posee_internet',
        'posee_computadora',
        'recibe_beca',
    ];

    protected $casts = [
        'posee_internet' => 'boolean',
        'posee_computadora' => 'boolean',
        'recibe_beca' => 'boolean',
        'numero_personas_hogar' => 'integer',
        'numero_aportantes' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
