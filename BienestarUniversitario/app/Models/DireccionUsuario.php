<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DireccionUsuario extends Model
{
    protected $table = 'direcciones_usuario';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_provincia',
        'id_canton',
        'direccion_referencia',
        'telefono_convencional',
        'correo_electronico',
        'nacionalidad',
        'id_tipo_direccion',
        'es_extranjero',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class, 'id_provincia');
    }

    public function canton(): BelongsTo
    {
        return $this->belongsTo(Canton::class, 'id_canton');
    }

    public function tipoDireccion(): BelongsTo
    {
        return $this->belongsTo(TipoDireccion::class, 'id_tipo_direccion');
    }
}
