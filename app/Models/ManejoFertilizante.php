<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManejoFertilizante extends Model
{
    use HasFactory;

    protected $table = 'manejo_fertilizantes';

    protected $fillable = [
        'bitacora_id',
        'tanque',
        'accion',
        'cantidad',
        'unidad_cantidad',
        'labores_culturales',
        'observaciones',
    ];

    public function bitacora()
    {
        return $this->belongsTo(SanidadNutricionBitacora::class, 'bitacora_id');
    }
}