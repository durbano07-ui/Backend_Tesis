<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogoInsumosOdontologia extends Model
{
    protected $table = 'catalogo_insumos_odontologia';

    protected $fillable = [
        'id_usuario_medico',
        'nombre',
        'stock',
    ];

    protected $casts = [
        'stock' => 'integer',
    ];

    public function medico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_medico');
    }
}
