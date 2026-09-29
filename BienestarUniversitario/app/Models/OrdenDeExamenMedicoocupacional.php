<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenDeExamenMedicoocupacional extends Model
{
    protected $table = 'orden_de_examen_medicoocupacional';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'fecha',
        'observaciones',
        'estado',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_doctor');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_paciente');
    }

    public function tiposExamen(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrdenDeExamenTipoMedicoocupacional::class, 'id_orden_examen');
    }

    public function otros(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrdenDeExamenOtrosMedicoocupacional::class, 'id_orden_examen');
    }
}
