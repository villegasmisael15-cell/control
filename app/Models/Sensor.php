<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    use HasFactory;

    protected $table = 'sensores_invernadero';

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
        'peso_bascula_1',
        'peso_bascula_2',
        'peso_hx711'
    ];
}