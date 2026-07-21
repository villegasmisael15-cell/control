<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'sectores',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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
     * Accesor para simular todos los sectores si el usuario es admin_general.
     * Evita tener que modificar filtros sector por sector en los controladores.
     */
    public function getSectoresAttribute($value)
    {
        if ($this->rol === 'admin_general') {
            // Obtenemos todos los sectores únicos registrados en el sistema y los unimos por comas
            return implode(',', \App\Models\SectorCaracteristica::pluck('sector')->toArray());
        }

        return $value;
    }
}