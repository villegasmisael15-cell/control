<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SectorCaracteristica extends Model
{
    protected $fillable = ['sector', 'superficie_m2', 'variedad', 'numero_plantas', 'macetas_por_gotero', 'fecha_trasplante'];
}
