<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdontogramaOdontologia extends Model
{
    protected $table = 'odontograma_odontologia';
    public $timestamps = false;
    protected $fillable = ['numero_pieza_dental'];

    public function carillas()
    {
        return $this->hasMany(OdontogramaPiezaCarilla::class, 'id_numero_pieza');
    }
}
