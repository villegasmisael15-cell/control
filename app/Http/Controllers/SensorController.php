<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sensor;

class SensorController extends Controller
{
    public function index()
    {
        // Consultas separadas por dispositivo para evitar confusiones en la vista
        $registrosElectrovolvulas = Sensor::where('esp32_id', 'ENTRADA ELECTROVALVULAS')->latest()->take(5)->get();
        $registrosInvernadero1 = Sensor::where('esp32_id', 'INVERNADERO 1')->latest()->take(5)->get();
        
        return view('telemetria.index', compact('registrosElectrovolvulas', 'registrosInvernadero1'));
    }

    public function almacenar(Request $request)
    {
        $id = DB::table('sensores_invernadero')->insertGetId([
            'esp32_id'          => $request->input('esp32_id', 'INVERNADERO 1'),
            'temp_ambiente'     => $request->input('temp_aht'),
            'humedad_ambiente'  => $request->input('hum_aht'),
            'calidad_aire_eco2' => $request->input('eco2'),
            'calidad_aire_tvoc' => $request->input('tvoc'),
            'luz_lux'           => $request->input('lux'),
            'temp_infrarrojo'   => $request->input('temp_obj'),
            'tds_valor'         => $request->input('tds_valor'),
            'he390_valor'       => $request->input('hw390_porcentaje'),
            'ph_valor'          => $request->input('ph_valor'),
            'temp_ds18b20'      => $request->input('temp_ds18b20'),
            'peso_bascula_1'    => $request->input('peso_bascula_1'),
            'peso_bascula_2'    => $request->input('peso_bascula_2'),
            'peso_bascula_3'    => $request->input('peso_bascula_3'),
            'peso_bascula_4'    => $request->input('peso_bascula_4'),
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