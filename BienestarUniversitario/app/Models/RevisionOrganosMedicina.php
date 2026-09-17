<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevisionOrganosMedicina extends Model
{
    protected $table = 'revision_organos_medicina';

    protected $fillable = [
        'id_usuario_doctor',
        'id_usuario_paciente',
        'detalle_revision_organos',
        'posicion_x',
        'posicion_y',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_doctor');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_paciente');
    }
}
