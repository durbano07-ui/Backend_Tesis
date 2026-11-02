<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductoFarmacia extends Model
{
    protected $table = 'productos_farmacia';

    /** Lista cerrada de vías de administración (igual a src/utils/viasAdministracion.js del frontend). */
    public const VIAS_ADMINISTRACION = [
        'Oral', 'Sublingual', 'Tópica', 'Oftálmica', 'Ótica', 'Nasal', 'Inhalatoria', 'Rectal',
        'Vaginal', 'Intramuscular', 'Intravenosa', 'Subcutánea', 'Intradérmica', 'Transdérmica',
    ];

    protected $fillable = [
        'codigo',
        'nombre',
        'id_presentacion',
        'via_administracion',
        'stock_cajas',
        'stock_unidades',
        'unidades_por_caja',
        'activo',
    ];

    protected $casts = [
        'stock_cajas' => 'integer',
        'stock_unidades' => 'integer',
        'unidades_por_caja' => 'integer',
        'activo' => 'boolean',
    ];

    /** Stock total en unidades (null si aún no se definió cuántas unidades trae la caja). */
    protected $appends = ['stock_total_unidades'];

    /** Umbral de stock bajo: en unidades si se conoce el total; si no, por cajas o sueltas. */
    public const UMBRAL_STOCK_BAJO = 10;

    public function getStockTotalUnidadesAttribute(): ?int
    {
        return $this->unidades_por_caja
            ? $this->stock_cajas * $this->unidades_por_caja + $this->stock_unidades
            : null;
    }

    public function esStockBajo(int $umbral = self::UMBRAL_STOCK_BAJO): bool
    {
        return $this->unidades_por_caja
            ? $this->stock_total_unidades <= $umbral
            : ($this->stock_cajas <= $umbral || $this->stock_unidades <= $umbral);
    }

    /** Misma regla de stock bajo para filtrar en la base de datos. */
    public function scopeStockBajo($query, int $umbral = self::UMBRAL_STOCK_BAJO)
    {
        return $query->whereRaw(
            'CASE WHEN unidades_por_caja IS NULL THEN (stock_cajas <= ? OR stock_unidades <= ?) '
            . 'ELSE (stock_cajas * unidades_por_caja + stock_unidades) <= ? END',
            [$umbral, $umbral, $umbral]
        );
    }

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
        // Sin unidades por caja no se pueden abrir cajas: cajas y sueltas se cuentan por separado
        if (!$this->unidades_por_caja) {
            return $this->stock_cajas >= $cajas && $this->stock_unidades >= $unidades;
        }

        return $cajas * $this->unidades_por_caja + $unidades <= $this->stock_total_unidades;
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

        if ($this->unidades_por_caja) {
            // Las cajas pedidas salen de las cerradas (si no alcanzan, se completan con unidades) y
            // las unidades salen de las sueltas, abriendo cajas cerradas cuando hace falta.
            $cajasEnteras = min($cajas, $this->stock_cajas);
            $unidadesPedidas = $unidades + ($cajas - $cajasEnteras) * $this->unidades_por_caja;
            $this->stock_cajas -= $cajasEnteras;
            while ($unidadesPedidas > $this->stock_unidades && $this->stock_cajas > 0) {
                $this->stock_cajas--;
                $this->stock_unidades += $this->unidades_por_caja;
            }
            $this->stock_unidades -= $unidadesPedidas;
        } else {
            $this->stock_cajas -= $cajas;
            $this->stock_unidades -= $unidades;
        }
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