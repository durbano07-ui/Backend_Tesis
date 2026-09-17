<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioTipoSangre extends Model
{
    use HasFactory;

    protected $table = 'usuario_tipo_sangre';

    protected $fillable = [
        'id_usuario',
        'id_tipo_sangre',
        'asignado_por_usuario',
        'asignado_por_rol',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function tipoSangre(): BelongsTo
    {
        return $this->belongsTo(TipoSangre::class, 'id_tipo_sangre');
    }

    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_por_usuario');
    }
}
