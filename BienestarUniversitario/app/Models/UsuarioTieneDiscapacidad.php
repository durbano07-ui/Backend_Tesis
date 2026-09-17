<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioTieneDiscapacidad extends Model
{
    protected $table = 'usuario_tiene_discapacidad';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'detalle_discapacidad',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
