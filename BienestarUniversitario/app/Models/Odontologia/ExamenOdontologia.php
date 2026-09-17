<?php

namespace App\Models\Odontologia;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamenOdontologia extends Model
{
    protected $table = 'examen_odontologia';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'piel',
        'labios',
        'carrillos',
        'paladar',
        'piso_de_la_boca',
        'lengua',
        'observaciones',
        'glándulas_salivales',
        'ganglios',
        'tejido_muscular',
        'atm',
        'maxilar_superior',
        'maxilar_inferior',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'id_usuario_doctor');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'id_usuario_paciente');
    }
}
