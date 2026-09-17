<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListadoCargoPersonalMedicoocupacional extends Model
{
    protected $table = 'listado_cargo_personal_medicoocupacional';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'detalle_cargo',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
