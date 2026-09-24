<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sensor;

class SensorController extends Controller
{
    public function index(Request $request)
    {
        // Dispositivo por defecto si no se selecciona ninguno
        $dispositivoSeleccionado = $request->get('esp32', 'INVERNADERO 1');

        // Filtrar los registros únicamente del ESP32 seleccionado
        $sensores = Sensor::where('esp32_id', $dispositivoSeleccionado)
                          ->latest()
                          ->paginate(15); 
        
        // Lista de tus dispositivos activos
        $dispositivos = ['INVERNADERO 1', 'ENTRADA ELECTROVALVULAS'];

        return view('telemetria.index', compact('sensores', 'dispositivos', 'dispositivoSeleccionado'));
    }

    public function almacenar(Request $request)
    {
        // Guardamos todos los datos directamente en la tabla mapeando cada llave del ESP32
        $id = DB::table('sensores_invernadero')->insertGetId([
            'esp32_id'          => $request->input('esp32_id', 'ESP32_INVERNADERO_1'),
            'temp_ambiente'     => $request->input('temp_ambient', $request->input('temp_ambiente')),
            'humedad_ambiente'  => $request->input('hum_ambient', $request->input('humedad_ambiente')),
            'calidad_aire_eco2' => $request->input('calidad_aire_eco2'),
            'calidad_aire_tvoc' => $request->input('calidad_aire_tvoc'),
            'luz_lux'           => $request->input('luz_lux'),
            'temp_infrarrojo'   => $request->input('temp_infrarrojo'),
            'tds_valor'         => $request->input('tds_valor'),
            'he390_valor'       => $request->input('he390_valor'),
            'ph_valor'          => $request->input('ph_valor'),
            'temp_ds18b20'      => $request->input('temp_ds18b20'),
            'peso_bascula_1'    => $request->input('peso_bascula_1'),
            'peso_bascula_2'    => $request->input('peso_bascula_2'),
            'peso_hx711'        => $request->input('peso_hx711', $request->input('peso', 0)),
            'created_at'        => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Datos guardados correctamente',
            'id_registro' => $id
        ], 200);
    }
}