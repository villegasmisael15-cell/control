<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'sectores',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function monitoreos()
    {
        return $this->hasMany(MonitoreoClimaRiego::class);
    }

    /**
     * ACCESOR PARA EL ROL:
     * Engaña al sistema únicamente en las lecturas de vistas y controladores.
     * Lee directamente el valor interno guardado en los atributos para no interferir con las actualizaciones.
     */
    public function getRolAttribute()
    {
        $realRol = $this->attributes['rol'] ?? null;
        
        if ($realRol === 'admin_general') {
            return 'administrador';
        }
        return $realRol;
    }

    /**
     * ACCESOR PARA LOS SECTORES:
     * Al ser admin_general en la base de datos, le entrega todos los sectores registrados.
     */
    public function getSectoresAttribute($value)
    {
        if (isset($this->attributes['rol']) && $this->attributes['rol'] === 'admin_general') {
            return implode(',', \App\Models\SectorCaracteristica::pluck('sector')->toArray());
        }

        return $value;
    }
}