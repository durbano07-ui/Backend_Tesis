<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecetaMedicoocupacional extends Model
{
    protected $table = 'receta_medicoocupacional';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'id_parte_diario',
        'fecha',
        'estado_enfermedad',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];
    
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_doctor');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_paciente');
    }

    public function parteDiario(): BelongsTo
    {
        return $this->belongsTo(ParteDiarioMedicina::class, 'id_parte_diario');
    }

    public function lineas(): HasMany
    {
        return $this->hasMany(LineaRecetaMedicoocupacional::class, 'id_receta');
    }

    public function cie10s(): HasMany
    {
        return $this->hasMany(RecetaCieMedicoocupacional::class, 'id_receta');
    }

    public function signosAlarma(): HasMany
    {
        return $this->hasMany(SignoAlarmaRecetaMedicoocupacional::class, 'id_receta');
    }

    public function recomendaciones(): HasMany
    {
        return $this->hasMany(RecomendacionNofarmacologicaRecetaMedicoocupacional::class, 'id_receta');
    }
}
