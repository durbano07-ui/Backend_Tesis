<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatosIdentificacion extends Model
{
    protected $table = 'datos_identificacion';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'apellido_paterno',
        'apellido_materno',
        'primer_nombre',
        'segundo_nombre',
        'numero_cedula',
        'fecha_nacimiento',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
