<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SensorController extends Controller
{
    public function almacenar(Request $request)
    {
        // Puedes agregar opcionalmente una validación o un token de seguridad básico
        // Guardamos los datos directamente en la tabla que creaste
        $id = DB::table('sensores_invernadero')->insertGetId([
            'esp32_id'          => $request->input('esp32_id', 'ESP32_INVERNADERO_1'),
            'temp_ambiente'     => $request->input('temp_ambiente'),
            'humedad_ambiente'  => $request->input('humedad_ambiente'),
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
            'peso_hx711'        => $request->input('peso_hx711'),
            'created_at'        => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Datos guardados correctamente',
            'id_registro' => $id
        ], 200);
    }
}