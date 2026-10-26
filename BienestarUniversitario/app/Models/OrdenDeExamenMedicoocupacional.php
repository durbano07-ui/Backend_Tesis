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
        'resultado_pdf_path',
        'resultado_pdf_nombre',
        'resultado_subido_at',
        'resultado_subido_por',
    ];

    // La ruta interna del archivo no se expone; el PDF se obtiene por su endpoint
    protected $hidden = ['resultado_pdf_path'];

    protected $appends = ['tiene_resultado'];

    protected $casts = [
        'resultado_subido_at' => 'datetime',
    ];

    public function getTieneResultadoAttribute(): bool
    {
        return $this->resultado_pdf_path !== null;
    }

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
