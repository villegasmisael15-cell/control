<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SanidadNutricionBitacora extends Model
{
    use HasFactory;

    protected $table = 'sanidad_nutricion_bitacoras';

    protected $fillable = [
        'fecha',
        'sector',
        'operador_id',
    ];

    /**
     * Relación con el operador que genera la bitácora.
     */
   public function operador()
    {
        return $this->belongsTo(User::class, 'operador_id');
    }

    /**
     * Relación con los registros de Agroquímicos.
     */
    public function agroquimicos()
    {
        return $this->hasMany(ManejoAgroquimico::class, 'bitacora_id');
    }

    /**
     * Relación con los registros de Fertilizantes.
     */
    public function fertilizantes()
    {
        return $this->hasMany(ManejoFertilizante::class, 'bitacora_id');
    }
}