<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioTieneHijo extends Model
{
    protected $table = 'usuario_tiene_hijos';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'numero',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
