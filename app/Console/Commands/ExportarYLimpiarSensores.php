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
    protected $description = 'Exporta los registros antiguos por cada zona a archivos CSV independientes, los envía por correo y los borra del servidor';

    public function handle()
    {
        $limiteFecha = Carbon::now()->subDays(7);
        $correoDestino = 'tucorreo@gmail.com'; // <--- Cambia esto por tu correo real

        // Definir las zonas y sus respectivos identificadores en la base de datos
        $zonas = [
            'invernadero1' => ['INVERNADERO 1', 'INVERNADERO_1'],
            'invernadero2' => ['INVERNADERO 2', 'INVERNADERO_2'],
            'invernadero3' => ['INVERNADERO 3', 'INVERNADERO_3'],
            'electrovalvulas' => ['ELECTROVALBULAS', 'ELECTROVÁLVULAS'],
        ];

        $archivosAdjuntos = [];
        $idsTotalBorrar = [];

        $rutaCarpeta = 'respaldos';
        Storage::makeDirectory($rutaCarpeta);

        foreach ($zonas as $key => $esp32Ids) {
            // Obtener registros de más de 7 días para esta zona específica
            $registros = DB::table('sensores_invernadero')
                            ->whereIn('esp32_id', $esp32Ids)
                            ->where('created_at', '<', $limiteFecha)
                            ->get();

            if ($registros->isEmpty()) {
                continue; // Si no hay registros en esta zona, pasamos a la siguiente
            }

            // Acumular IDs para borrar después
            $idsTotalBorrar = array_merge($idsTotalBorrar, $registros->pluck('id')->toArray());

            // Crear el CSV específico para esta zona
            $nombreArchivo = 'respaldo_' . $key . '_' . Carbon::now()->format('Y-m-d') . '.csv';
            $rutaCompleta = storage_path('app/' . $rutaCarpeta . '/' . $nombreArchivo);

            $file = fopen($rutaCompleta, 'w');

            // Definir columnas personalizadas según la zona
            if ($key === 'invernadero1') {
                fputcsv($file, ['ID', 'ESP32', 'Bascula 1', 'Bascula 2', 'pH', 'EC (mS)', 'Temp Amb', 'Hum Amb', 'Temp IR', 'Suelo HW390', 'Temp Suelo', 'eCO2', 'TVOC', 'Luz', 'Fecha']);
                foreach ($registros as $reg) {
                    fputcsv($file, [$reg->id, $reg->esp32_id, $reg->peso_bascula_1, $reg->peso_bascula_2, $reg->ph_valor, $reg->tds_valor, $reg->temp_ambiente, $reg->humedad_ambiente, $reg->temp_infrarrojo, $reg->he390_valor, $reg->temp_ds18b20, $reg->calidad_aire_eco2, $reg->calidad_aire_tvoc, $reg->luz_lux, $reg->created_at]);
                }
            } elseif ($key === 'invernadero2') {
                fputcsv($file, ['ID', 'ESP32', 'Bascula 1', 'Bascula 2', 'Suelo HW390', 'pH', 'EC (mS)', 'Temp Amb', 'Hum Amb', 'Temp IR', 'eCO2', 'Fecha']);
                foreach ($registros as $reg) {
                    fputcsv($file, [$reg->id, $reg->esp32_id, $reg->peso_bascula_1, $reg->peso_bascula_2, $reg->he390_valor, $reg->ph_valor, $reg->tds_valor, $reg->temp_ambiente, $reg->humedad_ambiente, $reg->temp_infrarrojo, $reg->calidad_aire_eco2, $reg->created_at]);
                }
            } elseif ($key === 'invernadero3') {
                fputcsv($file, ['ID', 'ESP32', 'Bascula 1', 'Bascula 2', 'Suelo HW390', 'pH', 'EC (mS)', 'Temp IR', 'Fecha']);
                foreach ($registros as $reg) {
                    fputcsv($file, [$reg->id, $reg->esp32_id, $reg->peso_bascula_1, $reg->peso_bascula_2, $reg->he390_valor, $reg->ph_valor, $reg->tds_valor, $reg->temp_infrarrojo, $reg->created_at]);
                }
            } elseif ($key === 'electrovalvulas') {
                fputcsv($file, ['ID', 'ESP32', 'pH', 'EC (mS)', 'Fecha']);
                foreach ($registros as $reg) {
                    fputcsv($file, [$reg->id, $reg->esp32_id, $reg->ph_valor, $reg->tds_valor, $reg->created_at]);
                }
            }

            fclose($file);
            $archivosAdjuntos[] = $rutaCompleta;
        }

        if (empty($archivosAdjuntos)) {
            $this->info('No hay registros antiguos en ninguna zona para respaldar.');
            return;
        }

        // Enviar correo con todos los respaldos adjuntos
        Mail::send([], [], function ($message) use ($correoDestino, $archivosAdjuntos) {
            $message->to($correoDestino)
                    ->subject('Respaldo Semanal - Telemetría de Invernaderos y Electroválvulas')
                    ->html('Adjunto encontrarás los archivos CSV con el respaldo semanal de cada zona. Los registros antiguos han sido depurados del servidor para optimizar el rendimiento.');

            foreach ($archivosAdjuntos as $path) {
                $message->attach($path, [
                    'mime' => 'text/csv',
                ]);
            }
        });

        $this->info("Correo con los respaldos enviado exitosamente a {$correoDestino}");

        // Borrar de la base de datos del servidor todos los registros respaldados
        DB::table('sensores_invernadero')->whereIn('id', $idsTotalBorrar)->delete();

        // Limpiar los archivos CSV temporales del servidor
        foreach ($archivosAdjuntos as $path) {
            @unlink($path);
        }

        $this->info("Se han limpiado un total de " . count($idsTotalBorrar) . " registros antiguos del servidor.");
    }
}