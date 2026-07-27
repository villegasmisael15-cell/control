<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    use HasFactory;

    // Nombre de la tabla asignada en la migración
    protected $table = 'reportes';

    // Campos habilitados para llenado/actualización
    protected $fillable = [
        'user_id',
        'sector',
        'total_kg',
        'rechazado_kg',
        'aceptados_kg',
        'empacados',
        'nacional',
        'procesado',
        'observaciones',
    ];

    /**
     * Relación: Un reporte pertenece a un Operador (Usuario)
     */
    public function operador()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}