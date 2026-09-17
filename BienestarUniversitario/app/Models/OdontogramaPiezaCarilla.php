<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdontogramaPiezaCarilla extends Model
{
    protected $table = 'odontograma_pieza_carilla_odontologia';
    public $timestamps = false;
    protected $fillable = ['id_numero_pieza', 'numero_carilla'];

    public function pieza()
    {
        return $this->belongsTo(OdontogramaOdontologia::class, 'id_numero_pieza');
    }
}
