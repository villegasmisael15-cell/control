<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\User;

class SectorUnico implements ValidationRule
{
    protected $userId;

    // Pasamos el ID del usuario actual para ignorarlo si estamos editando
    public function __construct($userId = null)
    {
        $this->userId = $userId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        // Limpiar y separar los sectores ingresados (ej: "Sector 1, Sector 2" -> ['Sector 1', 'Sector 2'])
        $sectoresNuevos = array_map('trim', explode(',', $value));

        // Obtener todos los usuarios excepto el que se está editando
        $usuarios = User::where('id', '!=', $this->userId)->whereNotNull('sectores')->get();

        foreach ($usuarios as $usuario) {
            $sectoresExistentes = array_map('trim', explode(',', $usuario->sectores));
            
            // Verificar si hay intersección (sectores repetidos)
            $repetidos = array_intersect($sectoresNuevos, $sectoresExistentes);

            if (!empty($repetidos)) {
                $fail('El o los siguientes sectores ya están asignados a otro usuario: ' . implode(', ', $repetidos) . '.');
                return;
            }
        }
    }
}