<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcedimientoOdontologia extends Model
{
    use HasFactory;

    protected $table = 'procedimientos_odontologia';

    protected $fillable = [
        'id_usuario_doctor',
        'nombre_procedimiento',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'id_usuario_doctor');
    }
}
