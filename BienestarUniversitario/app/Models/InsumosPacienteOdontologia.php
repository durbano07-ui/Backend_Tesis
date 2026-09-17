<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsumosPacienteOdontologia extends Model
{
    protected $table = 'insumos_paciente_odontologia';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'id_insumo',
        'cantidad_gastada',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_doctor');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_paciente');
    }

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(CatalogoInsumosOdontologia::class, 'id_insumo');
    }

    /**
     * Determina si el stock debe disminuirse basado en cantidad_gastada.
     */
    public function debeDisminuirStock(): bool
    {
        return $this->getCantidadADescontar() > 0;
    }

    /**
     * Determina la cantidad numérica a descontar.
     */
    public function getCantidadADescontar(): int
    {
        $valor = trim($this->cantidad_gastada);
        // Expresión regular para extraer el primer número (entero o decimal)
        if (preg_match('/^\d+(\.\d+)?/', $valor, $matches)) {
            $cantidad = (float) $matches[0];
            return (int) max(1, round($cantidad));
        }
        return 1; // Por defecto descuenta 1 si no se detecta número (ej: "un par")
    }
}
