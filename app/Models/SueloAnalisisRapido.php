<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SueloAnalisisRapido extends Model
{
    use HasFactory;

    protected $table = 'suelo_analisis_rapidos';

    protected $fillable = [
        'suelo_monitoreo_id',
        'tipo_analisis',
        'no3',
        'k',
        'ca',
        'na',
        'p',
        'ph',
        'ce'
    ];

    // Relación inversa: Este análisis pertenece a un monitoreo general
    public function sueloMonitoreo()
    {
        return $this->belongsTo(SueloMonitoreo::class, 'suelo_monitoreo_id');
    }
}