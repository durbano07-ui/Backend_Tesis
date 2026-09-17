<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FotoUsuario extends Model
{
    protected $table = 'foto_usuario';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'direccion_imagen',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
