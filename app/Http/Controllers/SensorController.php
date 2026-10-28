<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sensor;

class SensorController extends Controller
{
    public function index()
    {
        // Consultas para Invernadero 1
        $sensoresInvernadero = Sensor::whereIn('esp32_id', ['INVERNADERO 1', 'INVERNADERO_1'])
                                     ->latest()
                                     ->paginate(10, ['*'], 'inv_page');
                                     
        // Consultas para Invernadero 2
        $sensoresInvernadero2 = Sensor::whereIn('esp32_id', ['INVERNADERO 2', 'INVERNADERO_2'])
                                      ->latest()
                                      ->paginate(10, ['*'], 'inv2_page');

        // Consultas para Invernadero 3
        $sensoresInvernadero3 = Sensor::whereIn('esp32_id', ['INVERNADERO 3', 'INVERNADERO_3'])
                                      ->latest()
                                      ->paginate(10, ['*'], 'inv3_page');

        // Obtener la configuración de horarios para el Invernadero 3
        $configInv3 = DB::table('invernadero_configuraciones')->where('esp32_id', 'INVERNADERO 3')->first();
        
        return view('telemetria.index', compact('sensoresInvernadero', 'sensoresInvernadero2', 'sensoresInvernadero3', 'configInv3'));
    }

    // Método para guardar los cambios de horarios hechos desde la vista web
    public function actualizarConfiguracion(Request $request)
    {
        $esp32Id = $request->input('esp32_id', 'INVERNADERO 3');

        DB::table('invernadero_configuraciones')->updateOrInsert(
            ['esp32_id' => $esp32Id],
            [
                'hora_inicio' => $request->input('hora_inicio'),
                'hora_fin' => $request->input('hora_fin'),
                'intervalo_activo_ms' => $request->input('intervalo_activo_sec') * 1000,
                'intervalo_descanso_ms' => $request->input('intervalo_descanso_min') * 60000,
            ]
        );

        return back()->with('success', 'Configuración de horarios actualizada correctamente.');
    }

    public function almacenar(Request $request)
    {
        $esp32Id = $request->input('esp32_id', 'INVERNADERO 1');
        $pesoRecibido = $request->input('peso_hx711', $request->input('peso', 0));

        $id = DB::table('sensores_invernadero')->insertGetId([
            'esp32_id'          => $esp32Id,
            'temp_ambiente'     => $request->input('temp_ambiente', $request->input('temp_ambient')),
            'humedad_ambiente'  => $request->input('humedad_ambiente', $request->input('hum_ambient')),
            'calidad_aire_eco2' => $request->input('calidad_aire_eco2'),
            'calidad_aire_tvoc' => $request->input('calidad_aire_tvoc'),
            'luz_lux'           => $request->input('luz_lux'),
            'temp_infrarrojo'   => $request->input('temp_infrarrojo'),
            'tds_valor'         => $request->input('tds_valor'),
            'ads3_a1'           => $request->input('ads3_a1'),
            'ads3_a2'           => $request->input('ads3_a2'),
            'ads3_a3'           => $request->input('ads3_a3'),
            'he390_valor'       => $request->input('he390_valor'),
            'ads4_a1'           => $request->input('ads4_a1'),
            'ads4_a2'           => $request->input('ads4_a2'),
            'ads4_a3'           => $request->input('ads4_a3'),
            'ph_valor'          => $request->input('ph_valor'),
            'ads5_a1'           => $request->input('ads5_a1'),
            'ads5_a2'           => $request->input('ads5_a2'),
            'ads5_a3'           => $request->input('ads5_a3'),
            'temp_ds18b20'      => $request->input('temp_ds18b20'),
            'peso_bascula_1'    => $request->input('peso_bascula_1'),
            'peso_bascula_2'    => $request->input('peso_bascula_2'),
            'peso_hx711'        => $pesoRecibido,
            'created_at'        => now(),
        ]);

        // --- CONSULTAR HORARIOS DINÁMICOS DESDE LA BASE DE DATOS ---
        $config = DB::table('invernadero_configuraciones')->where('esp32_id', $esp32Id)->first();
        
        $intervaloMilisegundos = 3000; // Valor por defecto en caso de que no exista registro

        if ($config) {
            $horaActual = now()->format('H:i:s');
            // Validar si la hora actual está dentro del rango activo configurado
            if ($horaActual >= $config->hora_inicio && $horaActual <= $config->hora_fin) {
                $intervaloMilisegundos = $config->intervalo_activo_ms; 
            } else {
                $intervaloMilisegundos = $config->intervalo_descanso_ms; 
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Datos guardados correctamente',
            'id_registro' => $id,
            'siguiente_intervalo_ms' => $intervaloMilisegundos // El servidor le indica al ESP32 cuánto esperar
        ], 200);
    }
}