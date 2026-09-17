<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LineaRecetaMedicoocupacional extends Model
{
    protected $table = 'linea_receta_medicoocupacional';

    protected $fillable = [
        'id_receta',
        'id_producto_farmacia',
        'cantidad_cajas',
        'cantidad_unidades',
        'id_usuario_enfermera_despacho',
        'estado_despacho',
        'fecha_despacho',
        'detalle_medicamento',
        'detalle_dosis',
        'frecuencia_horas',
        'duracion_tratamiento_dias',
        'detalle_via_administracion',
        'detalle_numero_y_letras',
    ];

    protected $casts = [
        'cantidad_cajas' => 'integer',
        'cantidad_unidades' => 'integer',
        'fecha_despacho' => 'datetime',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(ProductoFarmacia::class, 'id_producto_farmacia');
    }

    public function enfermera(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_enfermera_despacho');
    }

    public function receta(): BelongsTo
    {
        return $this->belongsTo(RecetaMedicoocupacional::class, 'id_receta');
    }
}