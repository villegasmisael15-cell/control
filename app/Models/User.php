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
        'fcm_token',
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

    // Relación para los sectores que un operador seleccionó al registrarse
    public function sectoresOperador()
    {
        return $this->hasMany(OperadorSector::class, 'user_id');
    }

    // Relación para los invernaderos y sectores que posee un dueño
    public function sectorCaracteristicas()
    {
        return $this->hasMany(SectorCaracteristica::class, 'user_id');
    }

    /**
     * ACCESOR PARA EL ROL:
     * Engaña al sistema únicamente en las lecturas de vistas y controladores.
     */
    public function getRolAttribute()
    {
        $realRol = $this->attributes['rol'] ?? null;
        
        if ($realRol === 'admin_general') {
            return 'administrador';
        }
        return $realRol;
    }
}