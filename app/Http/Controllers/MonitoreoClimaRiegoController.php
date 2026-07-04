<?php

namespace App\Http\Controllers;

use App\Models\MonitoreoClimaRiego;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MonitoreoClimaRiegoController extends Controller
{
   public function index(Request $request)
{
    // 1. Inicializar la consulta base con Eager Loading
    $query = MonitoreoClimaRiego::with('user')->orderBy('fecha', 'desc');

    // 2. RESTRICCIÓN POR ROL / FILTROS DE BÚSQUEDA ADICIONALES
    if (auth()->user()->rol !== 'administrador') {
        $sectoresTexto = auth()->user()->sectores;
        $sectoresAsignados = $sectoresTexto ? array_map('trim', explode(',', $sectoresTexto)) : [];
        $query->whereIn('sector', $sectoresAsignados);
    } else {
        // --- BLOQUE EXCLUSIVO DE ADMINISTRADOR: BUSCADOR UNIFICADO ---
        if ($request->filled('buscar_termino')) {
            $termino = $request->input('buscar_termino');
            
            // Agrupamos con una función callback para evitar romper otros filtros como fechas
            $query->where(function ($q) use ($termino) {
                // Coincidencia directa por el nombre del sector
                $q->where('sector', 'LIKE', '%' . $termino . '%')
                  // O coincidencia a través de la relación con el operador
                  ->orWhereHas('user', function ($subQuery) use ($termino) {
                      $subQuery->where('name', 'LIKE', '%' . $termino . '%');
                  });
            });
        }
    }

    // 3. PROCESAR FILTROS DINÁMICOS (Semana / Mes)
    $semana = $request->input('semana');
    $mes = $request->input('mes');

    if ($request->filled('semana') && $request->filled('mes')) {
        if ($request->session()->get('ultimo_filtro') === 'mes') {
            $mes = null;
            $request->merge(['mes' => null]);
        } else {
            $semana = null;
            $request->merge(['semana' => null]);
        }
    }

    if (!empty($semana)) {
        $request->session()->put('ultimo_filtro', 'semana');
        [$year, $week] = explode('-W', $semana);
        $inicioSemana = \Illuminate\Support\Carbon::now()->setISODate($year, $week)->startOfWeek();
        $finSemana = \Illuminate\Support\Carbon::now()->setISODate($year, $week)->endOfWeek();
        $query->whereBetween('fecha', [$inicioSemana, $finSemana]);
    }

    if (!empty($mes)) {
        $request->session()->put('ultimo_filtro', 'mes');
        $inicioMes = \Illuminate\Support\Carbon::parse($mes)->startOfMonth();
        $finMes = \Illuminate\Support\Carbon::parse($mes)->endOfMonth();
        $query->whereBetween('fecha', [$inicioMes, $finMes]);
    }

    if (empty($semana) && empty($mes)) {
        $request->session()->forget('ultimo_filtro');
    }

    // 4. Obtener los registros finales ya filtrados
    $monitoreos = $query->get();

    return view('monitoreo.index', compact('monitoreos'));
}

    public function create()
    {
        $user = auth()->user();

        if ($user->rol === 'administrador') {
            // OBTENCIÓN DINÁMICA DE SECTORES DESDE LA BD
            $todosLosSectoresTexto = User::whereNotNull('sectores')->pluck('sectores')->toArray();

            $sectoresUnicos = [];
            foreach ($todosLosSectoresTexto as $cadena) {
                $partes = explode(',', $cadena);
                foreach ($partes as $sector) {
                    $sectorLimpio = trim($sector);
                    if (!empty($sectorLimpio)) {
                        $sectoresUnicos[] = $sectorLimpio;
                    }
                }
            }

            $sectores = array_unique($sectoresUnicos);
            sort($sectores);
        } else {
            // Si es un operador, mantiene solo sus sectores asignados
            $sectoresTexto = $user->sectores;
            $sectores = $sectoresTexto ? array_map('trim', explode(',', $sectoresTexto)) : [];
        }

        return view('monitoreo.create', compact('sectores'));
    }

    public function store(Request $request)
    {
        // Se inyecta automáticamente la hora actual del servidor antes de validar
        $request->merge([
            'radiacion_hora' => now()->format('H:i:s'),
            'user_id' => auth()->id()
        ]);

        // Se validan los datos (campos técnicos opcionales 'nullable')
        $request->validate([
            'fecha' => 'required|date',
            'sector' => 'required|string|max:255',
            'temperatura' => 'nullable|numeric',
            'humedad' => 'nullable|numeric',
            'vol_riego_entrada' => 'nullable|integer',
            'vol_drenaje_salida' => 'nullable|integer',
            'ce_entrada' => 'nullable|numeric',
            'ce_salida' => 'nullable|numeric',
            'ph_entrada' => 'nullable|numeric',
            'ph_salida' => 'nullable|numeric',
            'peso_tarde_anterior' => 'nullable|numeric',
            'peso_manana' => 'nullable|numeric',
            
            // Campos de Radiación Solar (Obligatorios)
            'radiacion_hora' => 'required',
            'radiacion_lectura' => 'required|integer|min:0',
            'radiacion_semaforo' => 'required|string|max:255',
            'radiacion_accion_tomada' => 'nullable|string',

            'user_id' => 'required|exists:users,id',
        ]);

        // --- CÁLCULOS AUTOMATIZADOS CON CONTROL DE NULOS (BACKEND) ---
        
        // 1. DPV e Inyección de Estatus General
        $dpv = null;
        $estatus_general = 'SIN DATOS CLIMA';

        if ($request->filled('temperatura') && $request->filled('humedad')) {
            $temp = $request->temperatura;
            $hum = $request->humedad;
            $dpv = round((0.61078 * exp((17.27 * $temp) / ($temp + 237.3))) * (1 - $hum / 100), 2);
            $estatus_general = ($dpv >= 0.8 && $dpv <= 1.4) ? 'ÓPTIMO' : 'REVISAR CLIMA';
        }

        // 2. Porcentaje Drenaje
        $porcentaje_drenaje = null;
        if ($request->filled('vol_riego_entrada') && $request->filled('vol_drenaje_salida') && $request->vol_riego_entrada > 0) {
            $porcentaje_drenaje = round(($request->vol_drenaje_salida / $request->vol_riego_entrada) * 100, 1);
        }

        // 3. Diferencias CE y pH
        $diferencia_ce = null;
        if ($request->filled('ce_entrada') && $request->filled('ce_salida')) {
            $diferencia_ce = round($request->ce_salida - $request->ce_entrada, 2);
        }

        $diferencia_ph = null;
        if ($request->filled('ph_entrada') && $request->filled('ph_salida')) {
            $diferencia_ph = round($request->ph_salida - $request->ph_entrada, 2);
        }

        // 4. Porcentaje Caída Nocturna
        $porcentaje_caida_nocturna = null;
        if ($request->filled('peso_tarde_anterior') && $request->filled('peso_manana') && $request->peso_tarde_anterior > 0) {
            $porcentaje_caida_nocturna = round((($request->peso_tarde_anterior - $request->peso_manana) / $request->peso_tarde_anterior) * 100, 1);
        }

        // Se crea el registro de forma masiva
        MonitoreoClimaRiego::create(array_merge($request->all(), [
            'dpv' => $dpv,
            'porcentaje_drenaje' => $porcentaje_drenaje,
            'diferencia_ce' => $diferencia_ce,
            'diferencia_ph' => $diferencia_ph,
            'porcentaje_caida_nocturna' => $porcentaje_caida_nocturna,
            'estatus_general' => $estatus_general,
        ]));

        return redirect()->route('monitoreo.index')->with('status', '¡Registro guardado con éxito!');
    }

   public function show($id)
{
    // 1. Buscar el registro técnico o lanzar 404 si no existe (con su operador precargado)
    $monitoreo = MonitoreoClimaRiego::with('user')->findOrFail($id);

    // 2. RESTRICCIÓN DE SEGURIDAD: Si es operador, verificar que el registro pertenezca a sus sectores
    if (auth()->user()->rol !== 'administrador') {
        $sectoresTexto = auth()->user()->sectores;
        $sectoresAsignados = $sectoresTexto ? array_map('trim', explode(',', $sectoresTexto)) : [];

        if (!in_array($monitoreo->sector, $sectoresAsignados)) {
            abort(403, 'No tienes permiso para ver este registro.');
        }
    }

    // 3. IMPLEMENTACIÓN DE CARACTERÍSTICAS: Obtener los datos fijos del sector consultado
    $caracteristicas = \App\Models\SectorCaracteristica::where('sector', $monitoreo->sector)->first();

    // 4. Retornar la vista inyectando ambas variables de forma compacta
    return view('monitoreo.show', compact('monitoreo', 'caracteristicas'));
}
    public function edit($id)
    {
        if (auth()->user()->rol !== 'administrador') {
            abort(403, 'Acción no autorizada.');
        }

        $monitoreo = MonitoreoClimaRiego::findOrFail($id);

        // Obtener los sectores de forma dinámica para el select de edición también
        $todosLosSectoresTexto = User::whereNotNull('sectores')->pluck('sectores')->toArray();
        $sectoresUnicos = [];
        foreach ($todosLosSectoresTexto as $cadena) {
            $partes = explode(',', $cadena);
            foreach ($partes as $sector) {
                $sectorLimpio = trim($sector);
                if (!empty($sectorLimpio)) {
                    $sectoresUnicos[] = $sectorLimpio;
                }
            }
        }
        $sectores = array_unique($sectoresUnicos);
        sort($sectores);

        $sectoresAsignados = $sectores;

    // Retornamos enviando ambas variables por seguridad
    return view('monitoreo.edit', compact('monitoreo', 'sectores', 'sectoresAsignados'));
}

    public function update(Request $request, $id)
    {
        if (auth()->user()->rol !== 'administrador') {
            abort(403, 'Acción no autorizada.');
        }

        $monitoreo = MonitoreoClimaRiego::findOrFail($id);

        // Validación adaptada para permitir nulos en edición si es necesario
        $request->validate([
            'fecha' => 'required|date',
            'sector' => 'required|string|max:255',
            'temperatura' => 'nullable|numeric',
            'humedad' => 'nullable|numeric',
            'vol_riego_entrada' => 'nullable|integer',
            'vol_drenaje_salida' => 'nullable|integer',
            'ce_entrada' => 'nullable|numeric',
            'ce_salida' => 'nullable|numeric',
            'ph_entrada' => 'nullable|numeric',
            'ph_salida' => 'nullable|numeric',
            'peso_tarde_anterior' => 'nullable|numeric',
            'peso_manana' => 'nullable|numeric',
            'radiacion_lectura' => 'required|integer|min:0',
            'radiacion_semaforo' => 'required|string|max:255',
            'radiacion_accion_tomada' => 'nullable|string',
        ]);

        // --- RE-CÁLCULOS AUTOMATIZADOS CON CONTROL DE NULOS ---
        $dpv = null;
        $estatus_general = 'SIN DATOS CLIMA';

        if ($request->filled('temperatura') && $request->filled('humedad')) {
            $temp = $request->temperatura;
            $hum = $request->humedad;
            $dpv = round((0.61078 * exp((17.27 * $temp) / ($temp + 237.3))) * (1 - $hum / 100), 2);
            $estatus_general = ($dpv >= 0.8 && $dpv <= 1.4) ? 'ÓPTIMO' : 'REVISAR CLIMA';
        }

        $porcentaje_drenaje = null;
        if ($request->filled('vol_riego_entrada') && $request->filled('vol_drenaje_salida') && $request->vol_riego_entrada > 0) {
            $porcentaje_drenaje = round(($request->vol_drenaje_salida / $request->vol_riego_entrada) * 100, 1);
        }

        $diferencia_ce = null;
        if ($request->filled('ce_entrada') && $request->filled('ce_salida')) {
            $diferencia_ce = round($request->ce_salida - $request->ce_entrada, 2);
        }

        $diferencia_ph = null;
        if ($request->filled('ph_entrada') && $request->filled('ph_salida')) {
            $diferencia_ph = round($request->ph_salida - $request->ph_entrada, 2);
        }

        $porcentaje_caida_nocturna = null;
        if ($request->filled('peso_tarde_anterior') && $request->filled('peso_manana') && $request->peso_tarde_anterior > 0) {
            $porcentaje_caida_nocturna = round((($request->peso_tarde_anterior - $request->peso_manana) / $request->peso_tarde_anterior) * 100, 1);
        }

        $monitoreo->update(array_merge($request->all(), [
            'dpv' => $dpv,
            'porcentaje_drenaje' => $porcentaje_drenaje,
            'diferencia_ce' => $diferencia_ce,
            'diferencia_ph' => $diferencia_ph,
            'porcentaje_caida_nocturna' => $porcentaje_caida_nocturna,
            'estatus_general' => $estatus_general,
        ]));

        return redirect()->route('monitoreo.index')->with('status', '¡Registro actualizado con éxito!');
    }

    public function destroy($id)
    {
        if (auth()->user()->rol !== 'administrador') {
            abort(403, 'Acción no autorizada.');
        }

        $monitoreo = MonitoreoClimaRiego::findOrFail($id);
        $monitoreo->delete();

        return redirect()->route('monitoreo.index')->with('status', 'El registro ha sido eliminado.');
    }

   public function exportarExcel($id)
{
    // Validación estricta de seguridad por rol
    if (auth()->user()->rol !== 'administrador') {
        abort(403, 'Acción no autorizada.');
    }

    // 1. Traemos el monitoreo de forma limpia
    $monitoreo = MonitoreoClimaRiego::findOrFail($id);
    
    // 2. Traemos las características del sector sin cargar relaciones que no existen
    $caracteristicas = \App\Models\SectorCaracteristica::where('sector', $monitoreo->sector)->first();

    // 3. Buscamos al usuario operador que tenga este sector asignado en su columna de texto
    $operador = \App\Models\User::where('sectores', 'LIKE', '%' . $monitoreo->sector . '%')
                                ->where('rol', '!=', 'administrador') // Asegurar que sea el operador de campo
                                ->first();

    // 4. Asignamos el nombre encontrado o un respaldo legible
    $operadorDueno = $operador ? $operador->name : 'Sin operador asignado';

    $nombreArchivo = "Reporte_Sector_" . str_replace(' ', '_', $monitoreo->sector) . "_ID_" . $monitoreo->id . ".xls";

    $headers = [
        "Content-Type"        => "application/vnd.ms-excel; charset=UTF-8",
        "Content-Disposition" => "attachment; filename=\"$nombreArchivo\"",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        "Expires"             => "0"
    ];

    $callback = function() use ($monitoreo, $caracteristicas, $operadorDueno) {
        $output = fopen('php://output', 'w');
        
        $html = '
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta charset="UTF-8">
            <style>
                table { border-collapse: collapse; font-family: Arial, sans-serif; }
                th { background-color: #1e3a8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #cbd5e1; }
                td { border: 1px solid #cbd5e1; padding: 6px; text-align: left; }
                .titulo-principal { font-size: 16px; font-weight: bold; color: #1e3a8a; text-align: center; }
                .seccion-header { background-color: #f1f5f9; font-weight: bold; color: #334155; }
                .subtitulo { font-weight: bold; background-color: #f8fafc; }
            </style>
        </head>
        <body>
            <table>
                <!-- Título del Reporte -->
                <tr>
                    <th colspan="4" class="titulo-principal" style="background-none; color:#1e3a8a; padding: 10px 0;">
                        BITÁCORA DE CONTROL HIDROPÓNICA
                    </th>
                </tr>
                <tr>
                    <td class="subtitulo">Fecha de Captura:</td>
                    <td>' . \Carbon\Carbon::parse($monitoreo->fecha)->format('d/m/Y') . '</td>
                    <td class="subtitulo">Operador Dueño del Sector:</td>
                    <td>' . htmlspecialchars($operadorDueno, ENT_QUOTES, 'UTF-8') . '</td>
                </tr>
                <tr>
                    <td class="subtitulo">Sector:</td>
                    <td colspan="3">' . htmlspecialchars($monitoreo->sector, ENT_QUOTES, 'UTF-8') . '</td>
                </tr>
                
                <!-- Separador -->
                <tr><td colspan="4" style="border:none; height:10px;"></td></tr>

                <!-- Sección 1 -->
                <tr>
                    <th colspan="4" class="seccion-header">1. CARACTERÍSTICAS INICIALES DEL ÁREA</th>
                </tr>
                <tr>
                    <td class="subtitulo">Superficie:</td>
                    <td>' . ($caracteristicas ? $caracteristicas->superficie_m2 : 'Sin datos') . ' m²</td>
                    <td class="subtitulo">Variedad Instalada:</td>
                    <td>' . ($caracteristicas ? htmlspecialchars($caracteristicas->variedad, ENT_QUOTES, 'UTF-8') : 'Sin datos') . '</td>
                </tr>
                <tr>
                    <td class="subtitulo">Fecha Trasplante:</td>
                    <td colspan="3">' . ($caracteristicas ? \Carbon\Carbon::parse($caracteristicas->fecha_trasplante)->format('d/m/Y') : 'Sin datos') . '</td>
                </tr>

                <!-- Separador -->
                <tr><td colspan="4" style="border:none; height:10px;"></td></tr>

                <!-- Sección 2 -->
                <tr>
                    <th colspan="4" class="seccion-header">2. VARIABLES MÉTRICAS Y BALANCES DIARIOS</th>
                </tr>
                <tr>
                    <td class="subtitulo">Temperatura Ambiente:</td>
                    <td>' . $monitoreo->temperatura . ' °C</td>
                    <td class="subtitulo">Humedad Relativa:</td>
                    <td>' . $monitoreo->humedad . ' %</td>
                </tr>
                <tr>
                    <td class="subtitulo">DPV Calculado:</td>
                    <td>' . $monitoreo->dpv . ' kPa</td>
                    <td class="subtitulo">Estatus General Clima:</td>
                    <td>' . htmlspecialchars($monitoreo->estatus_general, ENT_QUOTES, 'UTF-8') . '</td>
                </tr>
                <tr>
                    <td class="subtitulo">Vol. Riego Entrada:</td>
                    <td>' . number_format($monitoreo->vol_riego_entrada) . ' mL</td>
                    <td class="subtitulo">Vol. Drenaje Salida:</td>
                    <td>' . number_format($monitoreo->vol_drenaje_salida) . ' mL</td>
                </tr>
                <tr>
                    <td class="subtitulo">Porcentaje Drenaje:</td>
                    <td>' . $monitoreo->porcentaje_drenaje . ' %</td>
                    <td class="subtitulo">Caída Nocturna Sustrato:</td>
                    <td>' . $monitoreo->porcentaje_caida_nocturna . ' %</td>
                </tr>

                <!-- Separador -->
                <tr><td colspan="4" style="border:none; height:10px;"></td></tr>

                <!-- Sección 3 -->
                <tr>
                    <th colspan="4" class="seccion-header">3. PARÁMETROS QUÍMICOS Y DIFERENCIALES</th>
                </tr>
                <tr style="text-align:center; font-weight:bold; background-color:#f8fafc;">
                    <td>Parámetro</td>
                    <td>Entrada</td>
                    <td>Salida</td>
                    <td>Diferencial (Δ)</td>
                </tr>
                <tr>
                    <td class="subtitulo">Conductividad Eléctrica (CE)</td>
                    <td>' . $monitoreo->ce_entrada . '</td>
                    <td>' . $monitoreo->ce_salida . '</td>
                    <td style="font-weight:bold;">' . $monitoreo->diferencia_ce . '</td>
                </tr>
                <tr>
                    <td class="subtitulo">Potencial Hidrógeno (pH)</td>
                    <td>' . $monitoreo->ph_entrada . '</td>
                    <td>' . $monitoreo->ph_salida . '</td>
                    <td style="font-weight:bold;">' . $monitoreo->diferencia_ph . '</td>
                </tr>

                <!-- Separador -->
                <tr><td colspan="4" style="border:none; height:10px;"></td></tr>

                <!-- Sección 4 -->
                <tr>
                    <th colspan="4" class="seccion-header">4. RADIACIÓN SOLAR Y COMPORTAMIENTO</th>
                </tr>
                <tr>
                    <td class="subtitulo">Hora Captura:</td>
                    <td>' . \Carbon\Carbon::parse($monitoreo->radiacion_hora)->format('g:i a') . '</td>
                    <td class="subtitulo">Lectura Tomada:</td>
                    <td>' . number_format($monitoreo->radiacion_lectura) . ' Lux</td>
                </tr>
                <tr>
                    <td class="subtitulo">Semáforo Radiación:</td>
                    <td>' . htmlspecialchars($monitoreo->radiacion_semaforo, ENT_QUOTES, 'UTF-8') . '</td>
                    <td class="subtitulo">Acción Ejecutada:</td>
                    <td>' . ($monitoreo->radiacion_accion_tomada ? htmlspecialchars($monitoreo->radiacion_accion_tomada, ENT_QUOTES, 'UTF-8') : 'Ninguna') . '</td>
                </tr>
            </table>
        </body>
        </html>';

        fwrite($output, $html);
        fclose($output);
    };

    return response()->stream($callback, 200, $headers);
}

public function graficas(Request $request)
{
    $query = MonitoreoClimaRiego::orderBy('fecha', 'desc');

    // 1. RESTRICCIÓN O FILTRADO POR SECTOR
    if (auth()->user()->rol !== 'administrador') {
        // El operador solo puede ver sus sectores asignados
        $sectoresTexto = auth()->user()->sectores;
        $sectoresAsignados = $sectoresTexto ? array_map('trim', explode(',', $sectoresTexto)) : [];
        $query->whereIn('sector', $sectoresAsignados);
    } else {
        // El administrador filtra por el sector que elija en el select dinámico
        if ($request->filled('buscar_sector')) {
            $query->where('sector', $request->input('buscar_sector'));
        }
    }

    // 2. FILTRO POR MES
    $mes = $request->input('mes');
    if ($request->filled('mes')) {
        $inicioMes = \Illuminate\Support\Carbon::parse($mes)->startOfMonth();
        $finMes = \Illuminate\Support\Carbon::parse($mes)->endOfMonth();
        $query->whereBetween('fecha', [$inicioMes, $finMes]);
        
        $historicoReciente = $query->get();
    } else {
        // Comportamiento por defecto: Muestra los últimos 15 registros del sector seleccionado
        $historicoReciente = $query->take(15)->get();
    }

    // Invertir la colección para mantener el orden cronológico de izquierda a derecha
    $historico = $historicoReciente->reverse();

    // 3. Mapeo y formateo estricto a tipos primitivos de JavaScript
    $fechas   = $historico->pluck('fecha')->map(fn($f) => \Carbon\Carbon::parse($f)->format('d/m'))->toArray();
    $dpv      = $historico->pluck('dpv')->map(fn($val) => is_numeric($val) ? floatval($val) : 0)->toArray();
    $drenaje  = $historico->pluck('porcentaje_drenaje')->map(fn($val) => is_numeric($val) ? floatval($val) : 0)->toArray();
    $difCe    = $historico->pluck('diferencia_ce')->map(fn($val) => is_numeric($val) ? floatval($val) : 0)->toArray();
    $lux      = $historico->pluck('radiacion_lectura')->map(fn($val) => is_numeric($val) ? floatval($val) : 0)->toArray();

    return view('graficas.index', compact('fechas', 'dpv', 'drenaje', 'difCe', 'lux'));
}
}