<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ciclo extends Model
{
    protected $table = 'ciclos';
    public $timestamps = false;
    protected $fillable = ['numero'];
}
