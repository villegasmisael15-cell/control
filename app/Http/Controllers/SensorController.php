<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sensor;

class SensorController extends Controller
{
    public function index()
    {
        // Consultas separadas para alimentar las dos secciones de tu nueva vista
        $registrosElectrovolvulas = Sensor::where('esp32_id', 'ENTRADA ELECTROVALVULAS')->latest()->take(5)->get();
        $registrosInvernadero1 = Sensor::where('esp32_id', 'INVERNADERO 1')->latest()->take(5)->get();
        
        return view('telemetria.index', compact('registrosElectrovolvulas', 'registrosInvernadero1'));
    }

    public function almacenar(Request $request)
    {
        // Guardamos los datos mapeando todas las variantes posibles de nombres de llaves
        $id = DB::table('sensores_invernadero')->insertGetId([
            'esp32_id'          => $request->input('esp32_id', 'ESP32_INVERNADERO_1'),
            'temp_ambient'      => $request->input('temp_ambient', $request->input('temp_aht')),
            'humedad_ambiente'  => $request->input('humedad_ambiente', $request->input('hum_aht')),
            'calidad_aire_eco2' => $request->input('calidad_aire_eco2', $request->input('eco2')),
            'calidad_aire_tvoc' => $request->input('calidad_aire_tvoc', $request->input('tvoc')),
            'luz_lux'           => $request->input('luz_lux', $request->input('lux')),
            'temp_infrarrojo'   => $request->input('temp_infrarrojo', $request->input('temp_obj')),
            'tds_valor'         => $request->input('tds_valor'),
            'ads3_a1'           => $request->input('ads3_a1'),
            'ads3_a2'           => $request->input('ads3_a2'),
            'ads3_a3'           => $request->input('ads3_a3'),
            'he390_valor'       => $request->input('he390_valor', $request->input('hw390_porcentaje')),
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
            'peso_hx711'        => $request->input('peso_hx711', $request->input('peso')),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Datos guardados correctamente',
            'id_registro' => $id
        ], 200);
    }
}