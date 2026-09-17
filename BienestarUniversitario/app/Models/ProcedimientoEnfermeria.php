<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcedimientoEnfermeria extends Model
{
    protected $table = 'procedimientos_enfermeria';

    protected $fillable = [
        'id_usuario_doctor',
        'nombre_procedimiento',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_doctor');
    }
}
