<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    protected $table = 'provincia';
    public $timestamps = false;
    protected $fillable = ['nombre'];

    public function cantones()
    {
        return $this->hasMany(Canton::class, 'id_provincia');
    }
}
