<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvernaderoController extends Controller
{
    public function storeInvernadero2(Request $request)
    {
        // Validar los campos entrantes desde el ESP32
        $validated = $request->validate([
            'peso_1' => 'required|numeric',
            'peso_2' => 'required|numeric',
            'humedad_suelo' => 'required|integer',
            'ph' => 'required|numeric',
            'ec' => 'required|numeric',
            'temp_ir_objeto' => 'nullable|numeric',
            'temp_ir_ambiente' => 'nullable|numeric',
            'temp_ambiente' => 'required|integer',
            'humedad_ambiente' => 'required|integer',
            'aqi' => 'nullable|integer',
            'eco2' => 'nullable|integer',
        ]);

        // Insertar en la base de datos
        DB::table('invernadero_2_lecturas')->insert([
            'nodo' => 'invernadero_2',
            'peso_1' => $validated['peso_1'],
            'peso_2' => $validated['peso_2'],
            'humedad_suelo' => $validated['humedad_suelo'],
            'ph' => $validated['ph'],
            'ec' => $validated['ec'],
            'temp_ir_objeto' => $validated['temp_ir_objeto'] ?? null,
            'temp_ir_ambiente' => $validated['temp_ir_ambiente'] ?? null,
            'temp_ambiente' => $validated['temp_ambiente'],
            'humedad_ambiente' => $validated['humedad_ambiente'],
            'aqi' => $validated['aqi'] ?? 0,
            'eco2' => $validated['eco2'] ?? 0,
            'created_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Datos de Invernadero 2 guardados correctamente'
        ], 200);
    }
}
