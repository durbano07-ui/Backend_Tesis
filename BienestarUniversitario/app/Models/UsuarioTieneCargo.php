<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioTieneCargo extends Model
{
    protected $table = 'usuario_tiene_cargo';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'id_cargo',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_doctor');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_paciente');
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(ListadoCargoPersonalMedicoocupacional::class, 'id_cargo');
    }
}
