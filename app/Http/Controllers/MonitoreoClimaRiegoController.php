<?php

namespace App\Http\Controllers;

use App\Models\MonitoreoClimaRiego;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Exports\ReporteMonitoreoExport;
use Maatwebsite\Excel\Facades\Excel;

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
        // 1. Validar los datos
        $request->validate([
            'fecha' => 'required|date',
            'sector' => 'required|string|max:255',
            'temperatura' => 'nullable|numeric|min:-10|max:60',
            'humedad' => 'nullable|numeric|min:0|max:100',
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

        // 2. BUSCAR AL OPERADOR DUEÑO DEL SECTOR DE FORMA SEGURA
        $sectorBuscado = trim($request->sector);

        $operador = \App\Models\User::where('rol', '!=', 'administrador')
            ->whereRaw("TRIM(sectores) LIKE ?", ["%{$sectorBuscado}%"])
            ->first();

        // 🛡️ RESPALDO BLINDADO: Si el sector no tiene un operador asignado en producción, usa el usuario actual en sesión
        $userIdReal = $operador ? $operador->id : auth()->id();

        // 3. Lógica de riego por macetas
        $volRiego = $request->vol_riego_entrada;
        if (!is_null($volRiego)) {
            $caracteristica = \App\Models\SectorCaracteristica::where('sector', $request->sector)->first();
            $macetas = $caracteristica ? $caracteristica->macetas_por_gotero : 1;
            if ($macetas > 0) {
                $volRiego = (int) round($volRiego / $macetas);
            }
        }

        // 4. Cálculos automatizados
        $dpv = null;
        $estatus_general = 'SIN DATOS CLIMA';

        if ($request->filled('temperatura') && $request->filled('humedad')) {
            $temp = $request->temperatura;
            $hum = $request->humedad;
            $dpv = round((0.61078 * exp((17.27 * $temp) / ($temp + 237.3))) * (1 - $hum / 100), 2);
            $estatus_general = ($dpv >= 0.8 && $dpv <= 1.4) ? 'ÓPTIMO' : 'REVISAR CLIMA';
        }

        $porcentaje_drenaje = null;
        if (!is_null($volRiego) && $request->filled('vol_drenaje_salida') && $volRiego > 0) {
            $porcentaje_drenaje = round(($request->vol_drenaje_salida / $volRiego) * 100, 1);
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

        // 5. GUARDAR DIRECTAMENTE
        MonitoreoClimaRiego::create([
            'user_id' => $userIdReal,
            'fecha' => $request->fecha,
            'sector' => $request->sector,
            'temperatura' => $request->temperatura,
            'humedad' => $request->humedad,
            'dpv' => $dpv,
            'vol_riego_entrada' => $volRiego,
            'vol_drenaje_salida' => $request->vol_drenaje_salida,
            'porcentaje_drenaje' => $porcentaje_drenaje,
            'ce_entrada' => $request->ce_entrada,
            'ce_salida' => $request->ce_salida,
            'diferencia_ce' => $diferencia_ce,
            'ph_entrada' => $request->ph_entrada,
            'ph_salida' => $request->ph_salida,
            'diferencia_ph' => $diferencia_ph,
            'peso_tarde_anterior' => $request->peso_tarde_anterior,
            'peso_manana' => $request->peso_manana,
            'porcentaje_caida_nocturna' => $porcentaje_caida_nocturna,
            'estatus_general' => $estatus_general,
            'radiacion_hora' => now()->format('H:i:s'),
            'radiacion_lectura' => $request->radiacion_lectura,
            'radiacion_semaforo' => $request->radiacion_semaforo,
            'radiacion_accion_tomada' => $request->radiacion_accion_tomada,
        ]);

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
    if (auth()->user()->rol !== 'administrador') {
        abort(403, 'Acción no autorizada.');
    }

    $monitoreo = MonitoreoClimaRiego::findOrFail($id);
    $caracteristicas = \App\Models\SectorCaracteristica::where('sector', $monitoreo->sector)->first();

    $operador = \App\Models\User::where('sectores', 'LIKE', '%' . $monitoreo->sector . '%')
                                ->where('rol', '!=', 'administrador')
                                ->first();

    $operadorDueno = $operador ? $operador->name : 'Sin operador asignado';

    $nombreArchivo = "Reporte_Sector_" . str_replace(' ', '_', $monitoreo->sector) . "_ID_" . $monitoreo->id . ".xlsx";

    return Excel::download(new ReporteMonitoreoExport($monitoreo, $caracteristicas, $operadorDueno), $nombreArchivo);
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