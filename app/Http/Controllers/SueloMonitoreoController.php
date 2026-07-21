<?php

namespace App\Http\Controllers;

use App\Models\SueloMonitoreo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SueloMonitoreoController extends Controller
{
    public function index(Request $request)
    {
        // 1. Consulta base ordenando de forma cronológica descendente
        $query = SueloMonitoreo::with('user')->orderBy('fecha', 'desc');

        // 2. Control de accesos por Rol
        if (auth()->user()->rol !== 'administrador') {
            $sectoresTexto = auth()->user()->sectores;
            $sectoresAsignados = $sectoresTexto ? array_map('trim', explode(',', $sectoresTexto)) : [];
            $query->whereIn('sector', $sectoresAsignados);
        } else {
            // Buscador unificado para el Administrator
            if ($request->filled('buscar_termino')) {
                $termino = $request->input('buscar_termino');
                $query->where(function ($q) use ($termino) {
                    $q->where('sector', 'LIKE', '%' . $termino . '%')
                      ->orWhereHas('user', function ($subQuery) use ($termino) {
                          $subQuery->where('name', 'LIKE', '%' . $termino . '%');
                      });
                });
            }
        }

        // 3. Filtros temporales (Semana / Mes)
        $semana = $request->input('semana');
        $mes = $request->input('mes');

        if ($request->filled('semana') && $request->filled('mes')) {
            if ($request->session()->get('ultimo_filtro_suelo') === 'mes') {
                $mes = null;
                $request->merge(['mes' => null]);
            } else {
                $semana = null;
                $request->merge(['semana' => null]);
            }
        }

        if (!empty($semana)) {
            $request->session()->put('ultimo_filtro_suelo', 'semana');
            [$year, $week] = explode('-W', $semana);
            $inicioSemana = Carbon::now()->setISODate($year, $week)->startOfWeek();
            $finSemana = Carbon::now()->setISODate($year, $week)->endOfWeek();
            $query->whereBetween('fecha', [$inicioSemana, $finSemana]);
        }

        if (!empty($mes)) {
            $request->session()->put('ultimo_filtro_suelo', 'mes');
            $inicioMes = Carbon::parse($mes)->startOfMonth();
            $finMes = Carbon::parse($mes)->endOfMonth();
            $query->whereBetween('fecha', [$inicioMes, $finMes]);
        }

        if (empty($semana) && empty($mes)) {
            $request->session()->forget('ultimo_filtro_suelo');
        }

        // 💡 CORRECCIÓN AQUI: Mapeamos los resultados para buscar al dueño real del sector
        $monitoreos = $query->get()->map(function($monitoreo) {
            $dueno = User::where('sectores', 'LIKE', '%' . trim($monitoreo->sector) . '%')->first();
            $monitoreo->dueno_sector = $dueno ? $dueno->name : 'Sin asignar / General';
            return $monitoreo;
        });

        return view('suelo.index', compact('monitoreos'));
    }

  public function create()
    {
        $user = auth()->user();

        if ($user->rol === 'administrador') {
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
            $sectoresTexto = $user->sectores;
            $sectores = $sectoresTexto ? array_map('trim', explode(',', $sectoresTexto)) : [];
        }

        return view('suelo.create', compact('sectores'));
    }

    public function store(Request $request)
    {
        // 1. Identificamos dinámicamente quién es el verdadero dueño del sector enviado
        $sectorBuscado = trim($request->input('sector'));
        
        $duenoSector = User::where('sectores', 'LIKE', '%' . $sectorBuscado . '%')->first();
        
        // Si por alguna razón extraña no encontramos un dueño asignado, usamos el ID logueado como respaldo de seguridad
        $idDuenoReal = $duenoSector ? $duenoSector->id : auth()->id();

        // 2. Forzamos la hora actual y el ID del DUEÑO REAL del sector en el request antes de validar
        $request->merge([
            'radiacion_hora' => now()->format('H:i:s'),
            'user_id'        => $idDuenoReal // <-- AQUÍ SE REALIZA LA MAGIA
        ]);

        $request->validate([
            'fecha' => 'required|date',
            'sector' => 'required|string|max:255',
            'temperatura' => 'nullable|numeric',
            'humedad' => 'nullable|numeric',
            'lectura_tensiometro' => 'nullable|numeric',
            'tensiometro_estatus' => 'nullable|string|max:100',
            'ce' => 'nullable|numeric',
            'ph' => 'nullable|numeric',
            
            'radiacion_hora' => 'required',
            'radiacion_lectura' => 'required|integer|min:0',
            'radiacion_semaforo' => 'required|string|max:255',
            'radiacion_accion_tomada' => 'nullable|string',
            'user_id' => 'required|exists:users,id',

            // Alertas condicionales
            'alerta_opcion'           => 'nullable|array',
            'alerta_opcion.*'         => 'string|in:EPS,ECP',

            // Análisis Rápido
            'analisis_rapido_cumplio' => 'required|string|in:si,no',
            'rapido_no3'              => 'nullable|string|max:50',
            'rapido_k'                => 'nullable|string|max:50',
            'rapido_ca'               => 'nullable|string|max:50',
            'rapido_na'               => 'nullable|string|max:50',
            'rapido_p'                => 'nullable|string|max:50',
            'rapido_ph'               => 'nullable|string|max:50',
            'rapido_ce'               => 'nullable|string|max:50',

            // Análisis de Laboratorio
            'lab_mo'                  => 'nullable|string|max:50',
            'lab_p_bray'              => 'nullable|string|max:50',
            'lab_k'                   => 'nullable|string|max:50',
            'lab_mg'                  => 'nullable|string|max:50',
            'lab_na'                  => 'nullable|string|max:50',
            'lab_fe'                  => 'nullable|string|max:50',
            'lab_zn'                  => 'nullable|string|max:50',
            'lab_mn'                  => 'nullable|string|max:50',
            'lab_cu'                  => 'nullable|string|max:50',
            'lab_b'                   => 'nullable|string|max:50',
            'lab_s'                   => 'nullable|string|max:50',
            'lab_n_no3'               => 'nullable|string|max:50',
        ]);

        // --- PROCESAMIENTO BIOCLIMÁTICO AUTOMATIZADO ---
        $dpv = null;
        $estatus_general = 'SIN DATOS CLIMA';

        if ($request->filled('temperatura') && $request->filled('humedad')) {
            $temp = $request->temperatura;
            $hum = $request->humedad;
            $dpv = round((0.61078 * exp((17.27 * $temp) / ($temp + 237.3))) * (1 - $hum / 100), 2);
            $estatus_general = ($dpv >= 0.8 && $dpv <= 1.4) ? 'ÓPTIMO' : 'REVISAR CLIMA';
        }

        // --- PROCESAMIENTO DE ALERTAS DE CONDUCTIVIDAD ELÉCTRICA (CE) ---
        $alertaCeOpcion = null;
        if ($request->filled('ce') && (float)$request->ce > 3.0 && $request->has('alerta_opcion')) {
            $alertaCeOpcion = implode(', ', $request->alerta_opcion);
        }

        // --- MAPEADO FORZADO E INSERCIÓN BLINDADA ---
        $datosAGuardar = array_merge($request->all(), [
            'dpv'              => $dpv,
            'estatus_general'  => $estatus_general,
            'alerta_ce_opcion' => $alertaCeOpcion,
        ]);

        SueloMonitoreo::create($datosAGuardar);

        return redirect()->route('suelo.index')->with('status', '¡Registro de Suelo guardado con éxito!');
    }
}