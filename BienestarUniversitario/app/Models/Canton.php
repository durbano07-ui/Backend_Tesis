<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Canton extends Model
{
    protected $table = 'provincia_canton';
    public $timestamps = false;
    protected $fillable = ['id_provincia', 'nombre'];

    public function provincia()
    {
        return $this->belongsTo(Provincia::class, 'id_provincia');
    }
}
