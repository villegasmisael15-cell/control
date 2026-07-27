<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonitoreoClimaRiego extends Model
{
    use HasFactory;
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    // Le indicamos a Laravel el nombre exacto de la tabla
    protected $table = 'monitoreo_clima_riegos';

    // Habilitamos los campos para poder registrar datos de golpe
    protected $fillable = [
        'user_id',
        'fecha',
        'sector',
        'temperatura',
        'humedad',
        'dpv',
        'vol_riego_entrada',
        'vol_drenaje_salida',
        'porcentaje_drenaje',
        'ce_entrada',
        'ce_salida',
        'diferencia_ce',
        'ph_entrada',
        'ph_salida',
        'diferencia_ph',
        'peso_tarde_anterior',
        'peso_manana',
        'porcentaje_caida_nocturna',
        'estatus_general',
        'radiacion_hora',
        'radiacion_lectura',
        'radiacion_semaforo',
        'radiacion_accion_tomada',
    ];
}
