<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockHistory extends Model
{
    protected $table = 'stock_history';

    protected $fillable = [
        'id_producto_farmacia',
        'id_usuario',
        'tipo_movimiento',
        'cantidad_cajas',
        'cantidad_unidades',
        'stock_anterior_cajas',
        'stock_anterior_unidades',
        'stock_nuevo_cajas',
        'stock_nuevo_unidades',
        'descripcion',
        'id_linea_receta',
    ];

    protected $casts = [
        'cantidad_cajas' => 'integer',
        'cantidad_unidades' => 'integer',
        'stock_anterior_cajas' => 'integer',
        'stock_anterior_unidades' => 'integer',
        'stock_nuevo_cajas' => 'integer',
        'stock_nuevo_unidades' => 'integer',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(ProductoFarmacia::class, 'id_producto_farmacia');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function lineaReceta(): BelongsTo
    {
        return $this->belongsTo(LineaRecetaMedicoocupacional::class, 'id_linea_receta');
    }
}