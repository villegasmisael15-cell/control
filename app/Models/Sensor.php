<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    use HasFactory;

    // Indicar explícitamente el nombre real de la tabla en tu base de datos
    protected $table = 'sensores_invernadero';

    // Habilitar la asignación masiva para todos los campos que recibimos
    protected $fillable = [
        'esp32_id',
        'temp_ambiente',
        'humedad_ambiente',
        'calidad_aire_eco2',
        'calidad_aire_tvoc',
        'luz_lux',
        'temp_infrarrojo',
        'tds_valor',
        'ads3_a1',
        'ads3_a2',
        'ads3_a3',
        'he390_valor',
        'ads4_a1',
        'ads4_a2',
        'ads4_a3',
        'ph_valor',
        'ads5_a1',
        'ads5_a2',
        'ads5_a3',
        'temp_ds18b20',
        'peso_hx711'
    ];
}