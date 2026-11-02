<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcedimientoOdontologia extends Model
{
    use HasFactory;

    protected $table = 'procedimientos_odontologia';

    /** Procedimientos con los que empieza el catálogo de cada odontólogo (los reportes los reconocen por nombre). */
    public const PREDETERMINADOS = [
        'Profilaxis',
        'Fluorización',
        'Destartraje',
        'Rest. Provisional',
        'Rest. con Resina',
        'Desgaste',
        'Exodoncias',
        'Receta',
        'Orden de RX',
    ];

    protected $fillable = [
        'id_usuario_doctor',
        'nombre_procedimiento',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'id_usuario_doctor');
    }
}
