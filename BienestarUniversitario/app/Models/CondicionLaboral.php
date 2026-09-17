<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CondicionLaboral extends Model
{
    protected $table = 'condicion_laboral_medicoocupacional';
    public $timestamps = false;
    protected $fillable = ['nombre'];
}
