<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioTieneAlergia extends Model
{
    protected $table = 'usuario_tiene_alergias';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'detalle_alergia',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
