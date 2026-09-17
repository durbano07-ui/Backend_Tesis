<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductoFarmacia extends Model
{
    protected $table = 'productos_farmacia';

    protected $fillable = [
        'codigo',
        'nombre',
        'id_presentacion',
        'stock_cajas',
        'stock_unidades',
        'activo',
    ];

    protected $casts = [
        'stock_cajas' => 'integer',
        'stock_unidades' => 'integer',
        'activo' => 'boolean',
    ];

    public function presentacion(): BelongsTo
    {
        return $this->belongsTo(Presentacion::class, 'id_presentacion');
    }

    public function lineasReceta(): HasMany
    {
        return $this->hasMany(LineaRecetaMedicoocupacional::class, 'id_producto_farmacia');
    }

    public function historial(): HasMany
    {
        return $this->hasMany(StockHistory::class, 'id_producto_farmacia');
    }

    /**
     * Verificar si hay stock suficiente
     */
    public function tieneStock(int $cajas, int $unidades): bool
    {
        return $this->stock_cajas >= $cajas && $this->stock_unidades >= $unidades;
    }

    /**
     * Agregar stock (suma)
     */
    public function agregarStock(int $cajas, int $unidades, User $usuario, ?string $descripcion = null, ?int $lineaRecetaId = null): StockHistory
    {
        $stockAnteriorCajas = $this->stock_cajas;
        $stockAnteriorUnidades = $this->stock_unidades;

        $this->stock_cajas += $cajas;
        $this->stock_unidades += $unidades;
        $this->save();

        return StockHistory::create([
            'id_producto_farmacia' => $this->id,
            'id_usuario' => $usuario->id,
            'tipo_movimiento' => 'entrada',
            'cantidad_cajas' => $cajas,
            'cantidad_unidades' => $unidades,
            'stock_anterior_cajas' => $stockAnteriorCajas,
            'stock_anterior_unidades' => $stockAnteriorUnidades,
            'stock_nuevo_cajas' => $this->stock_cajas,
            'stock_nuevo_unidades' => $this->stock_unidades,
            'descripcion' => $descripcion,
            'id_linea_receta' => $lineaRecetaId,
        ]);
    }

    /**
     * Reducir stock (resta)
     */
    public function reducirStock(int $cajas, int $unidades, User $usuario, ?string $descripcion = null, ?int $lineaRecetaId = null): ?StockHistory
    {
        if (!$this->tieneStock($cajas, $unidades)) {
            return null;
        }

        $stockAnteriorCajas = $this->stock_cajas;
        $stockAnteriorUnidades = $this->stock_unidades;

        $this->stock_cajas -= $cajas;
        $this->stock_unidades -= $unidades;
        $this->save();

        return StockHistory::create([
            'id_producto_farmacia' => $this->id,
            'id_usuario' => $usuario->id,
            'tipo_movimiento' => $lineaRecetaId ? 'despacho' : 'salida',
            'cantidad_cajas' => $cajas,
            'cantidad_unidades' => $unidades,
            'stock_anterior_cajas' => $stockAnteriorCajas,
            'stock_anterior_unidades' => $stockAnteriorUnidades,
            'stock_nuevo_cajas' => $this->stock_cajas,
            'stock_nuevo_unidades' => $this->stock_unidades,
            'descripcion' => $descripcion,
            'id_linea_receta' => $lineaRecetaId,
        ]);
    }
}