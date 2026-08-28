<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    use HasFactory;

    // Indicar explícitamente el nombre real de la tabla en tu base de datos
    protected $table = 'sensores_invernadero';

    // Habilitar la asignación masiva para los campos que recibimos
    protected $fillable = [
        'esp32_id',
        'temp_ambiente',
        'humedad_ambiente',
        'calidad_aire_eco2',
        'calidad_aire_tvoc',
        'luz_lux',
        'temp_infrarrojo',
        'tds_valor',
        'he390_valor',
        'ph_valor',
        'temp_ds18b20',
        'peso_hx711'
    ];
}