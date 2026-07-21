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
        
        // 💡 NUEVOS CAMPOS AÑADIDOS:
        // Control de Alerta CE
        'alerta_ce_opcion',

        // Análisis Rápido
        'analisis_rapido_cumplio',
        'rapido_no3',
        'rapido_k',
        'rapido_ca',
        'rapido_na',
        'rapido_p',
        'rapido_ph',
        'rapido_ce',

        // Análisis de Laboratorio
        'lab_mo',
        'lab_p_bray',
        'lab_k',
        'lab_mg',
        'lab_na',
        'lab_fe',
        'lab_zn',
        'lab_mn',
        'lab_cu',
        'lab_b',
        'lab_s',
        'lab_n_no3',
    ];

    /**
     * Relación con el usuario/operador que realiza el registro.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}