<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManejoAgroquimico extends Model
{
    use HasFactory;

    protected $table = 'manejo_agroquimicos';

    protected $fillable = [
        'bitacora_id',
        'fecha_aplicacion',
        'aplicacion',
        'productor',
        'producto',
        'dosis',
        'unidad_dosis',
        'is_intervalo_seguridad',
        'variedad',
        'numero_plantas',
        'solucion_madre',
        'fecha_trasplante',
        'solucion_diaria',
        'observaciones',
    ];

    public function bitacora()
    {
        return $this->belongsTo(SanidadNutricionBitacora::class, 'bitacora_id');
    }
}