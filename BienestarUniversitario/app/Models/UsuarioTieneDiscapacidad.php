<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioTieneDiscapacidad extends Model
{
    use \App\Models\Concerns\EncryptsHealthData;

    /** OWASP ASVS V6.1.2: datos de salud cifrados en reposo */
    protected array $encryptedHealthFields = ['detalle_discapacidad'];

    protected $table = 'usuario_tiene_discapacidad';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'detalle_discapacidad',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
