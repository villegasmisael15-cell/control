<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SueloMonitoreo extends Model
{
    use HasFactory;

    // Indicamos explícitamente el nombre de la tabla de la base de datos
    protected $table = 'suelo_monitoreos';

    // Habilitamos los campos para poder usar SueloMonitoreo::create() de forma segura
    protected $fillable = [
        'fecha',
        'sector',
        'user_id',
        'temperatura',
        'humedad',
        'dpv',
        'estatus_general',
        'lectura_tensiometro',
        'tensiometro_estatus',
        'ce',
        'ph',
        'radiacion_hora',
        'radiacion_lectura',
        'radiacion_semaforo',
        'radiacion_accion_tomada',
    ];

    /**
     * Relación con el usuario/operador que realiza el registro.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}