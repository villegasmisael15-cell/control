<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SectorUnico implements ValidationRule
{
    protected $userId;

    public function __construct($userId = null)
    {
        $this->userId = $userId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        // Convertir y aplanar cualquier formato (string, array o array anidado) a un array plano de strings
        $elementos = [];
        
        if (is_array($value)) {
            array_walk_recursive($value, function($item) use (&$elementos) {
                if (is_string($item) || is_numeric($item)) {
                    $elementos[] = trim((string)$item);
                }
            });
        } elseif (is_string($value)) {
            $elementos = array_map('trim', explode(',', $value));
        }

        // Filtrar elementos vacíos
        $sectoresNuevos = array_filter($elementos);

        // Validar que el usuario no envíe elementos duplicados en su propia lista
        if (count($sectoresNuevos) !== count(array_unique($sectoresNuevos))) {
            $fail('Has ingresado elementos duplicados en tu lista.');
            return;
        }
    }
}