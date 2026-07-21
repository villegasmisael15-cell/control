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
     * Engaña a los @if(auth()->user()->rol === 'administrador') de las vistas y controladores.
     * A ojos del sistema, el 'admin_general' se comportará exactamente como un 'administrador'.
     */
    public function getRolAttribute($value)
    {
        if ($value === 'admin_general') {
            return 'administrador';
        }
        return $value;
    }

    /**
     * ACCESOR PARA LOS SECTORES:
     * Al ser un administrador, le entrega todos los sectores registrados para que vea todo.
     */
    public function getSectoresAttribute($value)
    {
        // Nota: Usamos $this->attributes['rol'] para leer el valor real de la base de datos
        if (isset($this->attributes['rol']) && $this->attributes['rol'] === 'admin_general') {
            return implode(',', \App\Models\SectorCaracteristica::pluck('sector')->toArray());
        }

        return $value;
    }
}