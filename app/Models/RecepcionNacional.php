<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecepcionNacional extends Model
{
    // Forzamos el nombre exacto de la tabla física de tu migración
    protected $table = 'recepcion_nacionales';

    // Campos permitidos para llenado masivo
   protected $fillable = [
    'semana_nacional',
    'fecha_nacional',
    'productor_id',
    'recepcion_exportacion_id',
    'sector_registro',
    'cajas_comercializar',
    'cajas_comercializar_ajustado', 
    'peso_comercializar',
    'peso_comercializar_ajustado',  
    'cajas_rechazo_procesado',
    'peso_rechazo_procesado',
    'peso_rechazo_procesado_original',
    'cajas_vacias_totales',
    'fue_ajustado',               
    'total_cajas',
    'peso_comercializar_original',
    'total_kg',
    'capturado_por_id',
    'ajustado_por_id',           
];

/**
 * Lógica Automática: Determina qué cajas comerciales tomar en cuenta
 */
public function getCajasComercialesVigentesAttribute()
{
    return $this->fue_ajustado ? $this->cajas_comercializar_ajustado : $this->cajas_comercializar;
}

/**
 * Lógica Automática: Determina qué peso comercial tomar en cuenta
 */
public function getPesoComercialVigenteAttribute()
{
    return $this->fue_ajustado ? $this->peso_comercializar_ajustado : $this->peso_comercializar;
}

    /**
     * Relación: Una recepción nacional pertenece a un productor (Usuario)
     */
    public function productor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'productor_id');
    }

    // Relación con los ajustes
    public function ajustes()
    {
        return $this->hasMany(AjusteRecepcion::class, 'recepcion_id');
    }

    // Accessor para obtener siempre el peso vigente
    public function getPesoVigenteAttribute()
    {
        $ultimoAjuste = $this->ajustes()->latest()->first();
        return $ultimoAjuste ? $ultimoAjuste->peso_nuevo : $this->peso_comercializar;
    }

    // Accessor para las cajas vigentes
    public function getCajasVigentesAttribute()
    {
        $ultimoAjuste = $this->ajustes()->latest()->first();
        return $ultimoAjuste ? $ultimoAjuste->cajas_nuevas : $this->cajas_comercializar;
    }

    public function recepcionExportacion()
{
    return $this->belongsTo(RecepcionExportacion::class, 'recepcion_exportacion_id');
}
}

