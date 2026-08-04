<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperadorSector extends Model
{
    use HasFactory;

    protected $table = 'operador_sectores';

    protected $fillable = [
        'user_id',
        'dueno_id',
        'invernadero',
        'sector',
    ];

    // Relación con el operador
    public function operador()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación con el dueño
    public function dueno()
    {
        return $this->belongsTo(User::class, 'dueno_id');
    }
}