<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecepcionExportacion extends Model
{
    // Forzamos el nombre exacto de tu tabla de exportación
    protected $table = 'recepcion_exportaciones';

    // Campos permitidos para llenado masivo
    protected $fillable = [
    'semana_exportacion',
    'fecha_exportacion',
    'productor_id',
    'sector_registro',
    'cajas_exportacion', 
    'peso_exportacion',
    'restituidas',       
    'pendientes',        
    'capturado_por_id',
];

    /**
     * Relación: Una recepción de exportación pertenece a un productor (Usuario)
     */
    public function productor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'productor_id');
    }
    public function recepcionesNacionales()
    {
        return $this->hasMany(RecepcionNacional::class, 'recepcion_exportacion_id');
    }
}