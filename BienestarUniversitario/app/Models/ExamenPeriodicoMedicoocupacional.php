<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamenPeriodicoMedicoocupacional extends Model
{
    protected $table = 'examenes_periodicos_medicoocupacional';

    protected $fillable = [
        'id_usuario_doctor',
        'anio',
        'mes',
        'cantidad',
    ];

    protected $casts = [
        'anio' => 'integer',
        'mes' => 'integer',
        'cantidad' => 'integer',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_doctor');
    }
}
