<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalNuevoMedicoocupacional extends Model
{
    protected $table = 'personal_nuevo_medicoocupacional';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'fecha_ingreso',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_doctor');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_paciente');
    }
}
