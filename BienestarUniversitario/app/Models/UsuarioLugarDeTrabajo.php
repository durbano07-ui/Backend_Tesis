<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioLugarDeTrabajo extends Model
{
    protected $table = 'usuario_lugar_de_trabajo';

    protected $fillable = [
        'id_usuario',
        'id_lugar_trabajo',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function lugarTrabajo(): BelongsTo
    {
        return $this->belongsTo(LugarTrabajo::class, 'id_lugar_trabajo');
    }
}
