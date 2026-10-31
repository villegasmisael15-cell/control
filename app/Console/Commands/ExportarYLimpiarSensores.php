<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ExportarYLimpiarSensores extends Command
{
    protected $signature = 'sensores:exportar-limpiar';
    protected $description = 'Exporta los registros antiguos por cada zona a archivos CSV independientes (sin ID), los envía por correo y los borra del servidor';

    public function handle()
    {
        $limiteFecha = Carbon::now()->subDays(7);
        $correoDestino = 'villegasmisael15@gmail.com';

        $zonas = [
            'invernadero1'    => ['INVERNADERO 1', 'INVERNADERO_1'],
            'invernadero2'    => ['INVERNADERO 2', 'INVERNADERO_2'],
            'invernadero3'    => ['INVERNADERO 3', 'INVERNADERO_3'],
            'electrovalvulas' => ['ELECTROVALBULAS', 'ELECTROVÁLVULAS'],
        ];

        $archivosAdjuntos = [];
        $idsTotalBorrar = [];
        $rutaCarpeta = 'respaldos';

        if (!Storage::exists($rutaCarpeta)) {
            Storage::makeDirectory($rutaCarpeta);
        }

        foreach ($zonas as $key => $esp32Ids) {
            $registros = DB::table('sensores_invernadero')
                            ->whereIn('esp32_id', $esp32Ids)
                            ->where('created_at', '<', $limiteFecha)
                            ->get();

            if ($registros->isEmpty()) {
                continue; 
            }

            $idsTotalBorrar = array_merge($idsTotalBorrar, $registros->pluck('id')->toArray());

            $nombreArchivo = 'respaldo_' . $key . '_' . Carbon::now()->format('Y-m-d') . '.csv';
            $rutaCompleta = Storage::path($rutaCarpeta . '/' . $nombreArchivo);

            $file = fopen($rutaCompleta, 'w');

            // Definir columnas sin ID y dar formato limpio a la fecha
            if ($key === 'invernadero1') {
                fputcsv($file, ['ESP32', 'Bascula 1', 'Bascula 2', 'pH', 'EC (mS)', 'Temp Amb', 'Hum Amb', 'Temp IR', 'Suelo HW390', 'Temp Suelo', 'eCO2', 'TVOC', 'Luz', 'Fecha y Hora']);
                foreach ($registros as $reg) {
                    $fechaFormateada = $reg->created_at ? Carbon::parse($reg->created_at)->format('d/m/Y H:i:s') : '--';
                    fputcsv($file, [$reg->esp32_id, $reg->peso_bascula_1, $reg->peso_bascula_2, $reg->ph_valor, $reg->tds_valor, $reg->temp_ambiente, $reg->humedad_ambiente, $reg->temp_infrarrojo, $reg->he390_valor, $reg->temp_ds18b20, $reg->calidad_aire_eco2, $reg->calidad_aire_tvoc, $reg->luz_lux, $fechaFormateada]);
                }
            } elseif ($key === 'invernadero2') {
                fputcsv($file, ['ESP32', 'Bascula 1', 'Bascula 2', 'Suelo HW390', 'pH', 'EC (mS)', 'Temp Amb', 'Hum Amb', 'Temp IR', 'eCO2', 'Fecha y Hora']);
                foreach ($registros as $reg) {
                    $fechaFormateada = $reg->created_at ? Carbon::parse($reg->created_at)->format('d/m/Y H:i:s') : '--';
                    fputcsv($file, [$reg->esp32_id, $reg->peso_bascula_1, $reg->peso_bascula_2, $reg->he390_valor, $reg->ph_valor, $reg->tds_valor, $reg->temp_ambiente, $reg->humedad_ambiente, $reg->temp_infrarrojo, $reg->calidad_aire_eco2, $fechaFormateada]);
                }
            } elseif ($key === 'invernadero3') {
                fputcsv($file, ['ESP32', 'Bascula 1', 'Bascula 2', 'Suelo HW390', 'pH', 'EC (mS)', 'Temp IR', 'Fecha y Hora']);
                foreach ($registros as $reg) {
                    $fechaFormateada = $reg->created_at ? Carbon::parse($reg->created_at)->format('d/m/Y H:i:s') : '--';
                    fputcsv($file, [$reg->esp32_id, $reg->peso_bascula_1, $reg->peso_bascula_2, $reg->he390_valor, $reg->ph_valor, $reg->tds_valor, $reg->temp_infrarrojo, $fechaFormateada]);
                }
            } elseif ($key === 'electrovalvulas') {
                fputcsv($file, ['ESP32', 'pH', 'EC (mS)', 'Fecha y Hora']);
                foreach ($registros as $reg) {
                    $fechaFormateada = $reg->created_at ? Carbon::parse($reg->created_at)->format('d/m/Y H:i:s') : '--';
                    fputcsv($file, [$reg->esp32_id, $reg->ph_valor, $reg->tds_valor, $fechaFormateada]);
                }
            }

            fclose($file);
            $archivosAdjuntos[] = $rutaCompleta;
        }

        if (empty($archivosAdjuntos)) {
            $this->info('No hay registros de más de 7 días de antigüedad para respaldar.');
            return;
        }

        $periodoTexto = "Registros anteriores a " . $limiteFecha->format('d/m/Y');

        Mail::send([], [], function ($message) use ($correoDestino, $archivosAdjuntos, $periodoTexto) {
            $message->to($correoDestino)
                    ->subject('Respaldo Semanal - Telemetría (' . $periodoTexto . ')')
                    ->html('<h3>Respaldo de Telemetría</h3><p>Adjunto encontrarás los archivos CSV correspondientes al periodo <b>' . $periodoTexto . '</b> (sin IDs). Estos datos han sido depurados del servidor.</p>');

            foreach ($archivosAdjuntos as $path) {
                $message->attach($path, [
                    'mime' => 'text/csv',
                ]);
            }
        });

        $this->info("Correo con los respaldos enviado exitosamente a {$correoDestino}");

        DB::table('sensores_invernadero')->whereIn('id', $idsTotalBorrar)->delete();

        foreach ($archivosAdjuntos as $path) {
            @unlink($path);
        }

        $this->info("Se han limpiado un total de " . count($idsTotalBorrar) . " registros antiguos del servidor.");
    }
}