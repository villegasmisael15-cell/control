<?php

namespace App\Http\Controllers;

use App\Models\MonitoreoClimaRiego;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Exports\ReporteMonitoreoExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\SectorCaracteristica;
use App\Models\OperadorSector;

class MonitoreoClimaRiegoController extends Controller
{
    public function index(Request $request)
    {
        $query = MonitoreoClimaRiego::with('user')->orderBy('fecha', 'desc');
        $user = auth()->user();

        if (in_array($user->rol, ['administrador', 'admin_general'])) {
            if ($request->filled('buscar_termino')) {
                $termino = $request->input('buscar_termino');
                $query->where(function ($q) use ($termino) {
                    $q->where('sector', 'LIKE', '%' . $termino . '%')
                        ->orWhere('invernadero', 'LIKE', '%' . $termino . '%')
                        ->orWhereHas('user', function ($subQuery) use ($termino) {
                            $subQuery->where('name', 'LIKE', '%' . $termino . '%');
                        });
                });
            }
        } elseif ($user->rol === 'dueno') {
            $sectoresDueño = SectorCaracteristica::where('user_id', $user->id)
                ->get()
                ->map(fn($item) => ['invernadero' => trim($item->invernadero), 'sector' => trim($item->sector)]);

            $query->where(function ($q) use ($sectoresDueño) {
                foreach ($sectoresDueño as $par) {
                    $q->orWhere(function ($sub) use ($par) {
                        $sub->where('invernadero', $par['invernadero'])
                            ->where('sector', $par['sector']);
                    });
                }
                if ($sectoresDueño->isEmpty()) {
                    $q->whereRaw('1 = 0');
                }
            });
        } elseif ($user->rol === 'operador') {
            // El operador ve estrictamente sus invernaderos y sectores elegidos
            $sectoresOperador = OperadorSector::where('user_id', $user->id)
                ->get()
                ->map(fn($item) => ['invernadero' => trim($item->invernadero), 'sector' => trim($item->sector)]);

            $query->where(function ($q) use ($sectoresOperador) {
                foreach ($sectoresOperador as $par) {
                    $q->orWhere(function ($sub) use ($par) {
                        $sub->where('invernadero', $par['invernadero'])
                            ->where('sector', $par['sector']);
                    });
                }
                if ($sectoresOperador->isEmpty()) {
                    $q->whereRaw('1 = 0');
                }
            });
        }

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
            $inicioSemana = Carbon::now()->setISODate($year, $week)->startOfWeek();
            $finSemana = Carbon::now()->setISODate($year, $week)->endOfWeek();
            $query->whereBetween('fecha', [$inicioSemana, $finSemana]);
        }

        if (!empty($mes)) {
            $request->session()->put('ultimo_filtro', 'mes');
            $inicioMes = Carbon::parse($mes)->startOfMonth();
            $finMes = Carbon::parse($mes)->endOfMonth();
            $query->whereBetween('fecha', [$inicioMes, $finMes]);
        }

        if (empty($semana) && empty($mes)) {
            $request->session()->forget('ultimo_filtro');
        }

        $monitoreos = $query->get();

        return view('monitoreo.index', compact('monitoreos'));
    }

    public function create()
    {
        $user = auth()->user();

        if (in_array($user->rol, ['administrador', 'admin_general'])) {
            $sectores = SectorCaracteristica::all();
        } elseif ($user->rol === 'dueno') {
            $sectores = SectorCaracteristica::where('user_id', $user->id)->get();
        } else {
            // Operador: Carga exclusivamente los sectores y los invernaderos que eligió en OperadorSector
            $asignaciones = OperadorSector::where('user_id', $user->id)->get();
            $sectores = collect();
            foreach ($asignaciones as $asig) {
                $encontrado = SectorCaracteristica::where('user_id', $asig->dueno_id)
                    ->where('invernadero', trim($asig->invernadero))
                    ->where('sector', trim($asig->sector))
                    ->first();
                if ($encontrado) {
                    $sectores->push($encontrado);
                }
            }
        }

        return view('monitoreo.create', compact('sectores'));
    }   

    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            $sectorBuscado = trim($request->input('sector'));
            $invernaderoBuscado = trim($request->input('invernadero'));

            // VALIDACIÓN ESTRICTA PARA OPERADOR: Debe pertenecer obligatoriamente a sus sectores elegidos
            if ($user->rol === 'operador') {
                $tienePermiso = OperadorSector::where('user_id', $user->id)
                    ->where('invernadero', $invernaderoBuscado)
                    ->where('sector', $sectorBuscado)
                    ->exists();

                if (!$tienePermiso) {
                    abort(403, 'No tienes permiso para registrar en este invernadero y sector.');
                }
            }

            $idDuenoReal = $user->id;
            if (!empty($sectorBuscado) && !empty($invernaderoBuscado)) {
                $caracteristicaSector = SectorCaracteristica::where('sector', $sectorBuscado)
                    ->where('invernadero', $invernaderoBuscado)
                    ->first();

                if ($caracteristicaSector && User::where('id', $caracteristicaSector->user_id)->exists()) {
                    $idDuenoReal = $caracteristicaSector->user_id;
                }
            }

            $request->merge([
                'radiacion_hora' => now()->format('H:i:s'),
                'user_id'        => $idDuenoReal
            ]);

            $request->validate([
                'fecha'                   => 'required|date',
                'sector'                  => 'required|string|max:255',
                'invernadero'             => 'required|string|max:255',
                'temperatura'             => 'nullable|numeric',
                'humedad'                 => 'nullable|numeric',
                'vol_riego_entrada'       => 'nullable|numeric',
                'vol_drenaje_salida'      => 'nullable|numeric',
                'ce_entrada'              => 'nullable|numeric',
                'ce_salida'               => 'nullable|numeric',
                'ph_entrada'              => 'nullable|numeric',
                'ph_salida'               => 'nullable|numeric',
                'peso_tarde_anterior'     => 'nullable|numeric',
                'peso_manana'             => 'nullable|numeric',
                'radiacion_lectura'       => 'nullable|integer|min:0',
                'radiacion_semaforo'      => 'nullable|string|max:255',
                'radiacion_accion_tomada' => 'nullable|string',
                'user_id'                 => 'required|exists:users,id',
                'abejorros_flores'        => 'nullable|integer|min:0',
            ]);

            $volRiego = $request->vol_riego_entrada;
            if (!is_null($volRiego)) {
                $caracteristica = SectorCaracteristica::where('sector', $request->sector)
                    ->where('invernadero', $request->invernadero)
                    ->first();
                $macetas = $caracteristica ? $caracteristica->macetas_por_gotero : 1;
                if ($macetas > 0) {
                    $volRiego = (int) round($volRiego / $macetas);
                }
            }

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

            $abejorrosSemaforo = null;
            if ($request->filled('abejorros_flores')) {
                $flores = (int) $request->abejorros_flores;
                if ($flores >= 25 && $flores <= 30) {
                    $abejorrosSemaforo = 'VERDE';
                } elseif ($flores >= 20 && $flores <= 24) {
                    $abejorrosSemaforo = 'AMARILLO';
                } else {
                    $abejorrosSemaforo = 'ROJO';
                }
            }

            $datosAGuardar = array_merge($request->all(), [
                'dpv' => $dpv,
                'porcentaje_drenaje' => $porcentaje_drenaje,
                'diferencia_ce' => $diferencia_ce,
                'diferencia_ph' => $diferencia_ph,
                'porcentaje_caida_nocturna' => $porcentaje_caida_nocturna,
                'estatus_general' => $estatus_general,
                'vol_riego_entrada' => $volRiego,
                'abejorros_flores' => $request->abejorros_flores,
                'abejorros_semaforo' => $abejorrosSemaforo,
            ]);

            MonitoreoClimaRiego::create($datosAGuardar);

            // Evaluar alerta de drenaje exclusiva para administrador
            if (!is_null($porcentaje_drenaje) && ($porcentaje_drenaje < 10 || $porcentaje_drenaje > 35)) {
                $this->enviarAlertaAdministradores($request->invernadero, $request->sector, $porcentaje_drenaje, 'drenaje');
            }

            // Evaluar alerta de temperatura exclusiva para administrador
            if ($request->filled('temperatura')) {
                $tempVal = $request->temperatura;
                if ($tempVal > 35 || $tempVal < 4) {
                    $this->enviarAlertaAdministradores($request->invernadero, $request->sector, $tempVal, 'temperatura');
                }
            }

            return redirect()->route('monitoreo.index')->with('status', '¡Registro guardado con éxito!');

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine()
            ], 500);
        }
    }

    public function show($id)
    {
        $monitoreo = MonitoreoClimaRiego::with('user')->findOrFail($id);
        $user = auth()->user();

        if ($user->rol === 'operador') {
            $tieneAcceso = OperadorSector::where('user_id', $user->id)
                ->where('invernadero', $monitoreo->invernadero)
                ->where('sector', $monitoreo->sector)
                ->exists();

            if (!$tieneAcceso) {
                abort(403, 'No tienes permiso para ver este registro.');
            }
        } elseif ($user->rol === 'dueno') {
            $tieneAcceso = SectorCaracteristica::where('user_id', $user->id)
                ->where('invernadero', $monitoreo->invernadero)
                ->where('sector', $monitoreo->sector)
                ->exists();

            if (!$tieneAcceso) {
                abort(403, 'No tienes permiso para ver este registro.');
            }
        }

        $caracteristicas = SectorCaracteristica::where('user_id', $monitoreo->user_id)
            ->where('invernadero', $monitoreo->invernadero)
            ->where('sector', $monitoreo->sector)
            ->first();

        return view('monitoreo.show', compact('monitoreo', 'caracteristicas'));
    }

    public function edit($id)
    {
        $monitoreo = MonitoreoClimaRiego::findOrFail($id);
        $user = auth()->user();

        if (in_array($user->rol, ['administrador', 'admin_general'])) {
            $sectores = SectorCaracteristica::all();
        } elseif ($user->rol === 'dueno') {
            $sectores = SectorCaracteristica::where('user_id', $user->id)->get();
        } else {
            $tieneAcceso = OperadorSector::where('user_id', $user->id)
                ->where('invernadero', $monitoreo->invernadero)
                ->where('sector', $monitoreo->sector)
                ->exists();

            if (!$tieneAcceso) {
                abort(403, 'No tienes permiso para editar este registro.');
            }
            // El operador solo puede elegir entre los sectores que tiene asignados en su perfil
            $asignaciones = OperadorSector::where('user_id', $user->id)->get();
            $sectores = collect();
            foreach ($asignaciones as $asig) {
                $encontrado = SectorCaracteristica::where('user_id', $asig->dueno_id)
                    ->where('invernadero', trim($asig->invernadero))
                    ->where('sector', trim($asig->sector))
                    ->first();
                if ($encontrado) {
                    $sectores->push($encontrado);
                }
            }
        }

        return view('monitoreo.edit', compact('monitoreo', 'sectores'));
    }

    public function update(Request $request, $id)
    {
        $monitoreo = MonitoreoClimaRiego::findOrFail($id);
        $user = auth()->user();

        if ($user->rol === 'operador') {
            $tieneAcceso = OperadorSector::where('user_id', $user->id)
                ->where('invernadero', $monitoreo->invernadero)
                ->where('sector', $monitoreo->sector)
                ->exists();

            if (!$tieneAcceso) {
                abort(403, 'No tienes permiso para actualizar este registro.');
            }
        } elseif ($user->rol === 'dueno') {
            $tieneAcceso = SectorCaracteristica::where('user_id', $user->id)
                ->where('invernadero', $monitoreo->invernadero)
                ->where('sector', $monitoreo->sector)
                ->exists();

            if (!$tieneAcceso) {
                abort(403, 'No tienes permiso para actualizar este registro.');
            }
        }

        $request->validate([
            'fecha' => 'required|date',
            'sector' => 'required|string|max:255',
            'invernadero' => 'required|string|max:255',
            'temperatura' => 'nullable|numeric',
            'humedad' => 'nullable|numeric',
            'vol_riego_entrada' => 'nullable|numeric',
            'vol_drenaje_salida' => 'nullable|numeric',
            'ce_entrada' => 'nullable|numeric',
            'ce_salida' => 'nullable|numeric',
            'ph_entrada' => 'nullable|numeric',
            'ph_salida' => 'nullable|numeric',
            'peso_tarde_anterior' => 'nullable|numeric',
            'peso_manana' => 'nullable|numeric',
            'radiacion_lectura' => 'nullable|integer|min:0',
            'radiacion_semaforo' => 'nullable|string|max:255',
            'radiacion_accion_tomada' => 'nullable|string',
            'abejorros_flores' => 'nullable|integer|min:0',
        ]);

        $volRiego = $request->vol_riego_entrada;
        if (!is_null($volRiego)) {
            $caracteristica = SectorCaracteristica::where('sector', $request->sector)
                ->where('invernadero', $request->invernadero)
                ->first();
            $macetas = $caracteristica ? $caracteristica->macetas_por_gotero : 1;
            if ($macetas > 0) {
                $volRiego = (int) round($volRiego / $macetas);
            }
        }

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

        $abejorrosSemaforo = null;
        if ($request->filled('abejorros_flores')) {
            $flores = (int) $request->abejorros_flores;
            if ($flores >= 25 && $flores <= 30) {
                $abejorrosSemaforo = 'VERDE';
            } elseif ($flores >= 20 && $flores <= 24) {
                $abejorrosSemaforo = 'AMARILLO';
            } else {
                $abejorrosSemaforo = 'ROJO';
            }
        }

        $monitoreo->update(array_merge($request->all(), [
            'dpv' => $dpv,
            'porcentaje_drenaje' => $porcentaje_drenaje,
            'diferencia_ce' => $diferencia_ce,
            'diferencia_ph' => $diferencia_ph,
            'porcentaje_caida_nocturna' => $porcentaje_caida_nocturna,
            'estatus_general' => $estatus_general,
            'vol_riego_entrada' => $volRiego,
            'abejorros_flores' => $request->abejorros_flores,
            'abejorros_semaforo' => $abejorrosSemaforo,
        ]));

        if (!is_null($porcentaje_drenaje) && ($porcentaje_drenaje < 10 || $porcentaje_drenaje > 35)) {
            $this->enviarAlertaAdministradores($request->invernadero, $request->sector, $porcentaje_drenaje, 'drenaje');
        }

        if ($request->filled('temperatura')) {
            $tempVal = $request->temperatura;
            if ($tempVal > 35 || $tempVal < 4) {
                $this->enviarAlertaAdministradores($request->invernadero, $request->sector, $tempVal, 'temperatura');
            }
        }

        return redirect()->route('monitoreo.index')->with('status', '¡Registro actualizado con éxito!');
    }

    public function destroy($id)
    {
        if (!in_array(auth()->user()->rol, ['administrador', 'admin_general'])) {
            abort(403, 'Acción no autorizada.');
        }

        $monitoreo = MonitoreoClimaRiego::findOrFail($id);
        $monitoreo->delete();

        return redirect()->route('monitoreo.index')->with('status', 'El registro ha sido eliminado.');
    }

   public function graficas(Request $request)
    {
        $query = MonitoreoClimaRiego::query();
        $user = auth()->user();

        // 1. Control de accesos por rol
        if ($user->rol === 'operador') {
            $sectoresOperador = OperadorSector::where('user_id', $user->id)
                ->get()
                ->map(fn($item) => ['invernadero' => trim($item->invernadero), 'sector' => trim($item->sector)]);

            $query->where(function ($q) use ($sectoresOperador) {
                foreach ($sectoresOperador as $par) {
                    $q->orWhere(function ($sub) use ($par) {
                        $sub->where('invernadero', $par['invernadero'])
                            ->where('sector', $par['sector']);
                    });
                }
                if ($sectoresOperador->isEmpty()) {
                    $q->whereRaw('1 = 0');
                }
            });
        } elseif ($user->rol === 'dueno') {
            $sectoresDueño = SectorCaracteristica::where('user_id', $user->id)
                ->get()
                ->map(fn($item) => ['invernadero' => trim($item->invernadero), 'sector' => trim($item->sector)]);

            $query->where(function ($q) use ($sectoresDueño) {
                foreach ($sectoresDueño as $par) {
                    $q->orWhere(function ($sub) use ($par) {
                        $sub->where('invernadero', $par['invernadero'])
                            ->where('sector', $par['sector']);
                    });
                }
                if ($sectoresDueño->isEmpty()) {
                    $q->whereRaw('1 = 0');
                }
            });
        } else {
            // Filtros en cascada para Administradores / Admin General
            if ($request->filled('dueno_id')) {
                $sectoresDelDueno = SectorCaracteristica::where('user_id', $request->dueno_id)
                    ->get()
                    ->map(fn($item) => ['invernadero' => trim($item->invernadero), 'sector' => trim($item->sector)]);

                $query->where(function ($q) use ($sectoresDelDueno) {
                    foreach ($sectoresDelDueno as $par) {
                        $q->orWhere(function ($sub) use ($par) {
                            $sub->where('invernadero', $par['invernadero'])
                                ->where('sector', $par['sector']);
                        });
                    }
                    if ($sectoresDelDueno->isEmpty()) {
                        $q->whereRaw('1 = 0');
                    }
                });
            }

            if ($request->filled('invernadero')) {
                $query->where('invernadero', $request->input('invernadero'));
            }

            if ($request->filled('buscar_sector')) {
                $query->where('sector', $request->input('buscar_sector'));
            }
        }

        // 2. Filtro por mes o últimos 15 registros
        if ($request->filled('mes')) {
            $mesInput = $request->input('mes'); // Formato esperado: "YYYY-MM"
            $query->whereYear('fecha', '=', substr($mesInput, 0, 4))
                  ->whereMonth('fecha', '=', substr($mesInput, 5, 2));
            
            $historicoReciente = $query->orderBy('fecha', 'asc')->get();
        } else {
            $historicoReciente = $query->orderBy('fecha', 'desc')->take(15)->get()->reverse();
        }

        $historico = $historicoReciente;

        // 3. Extracción de variables para las gráficas
        $fechas  = $historico->pluck('fecha')->map(fn($f) => Carbon::parse($f)->format('d/m'))->toArray();
        $dpv     = $historico->pluck('dpv')->map(fn($val) => is_numeric($val) ? floatval($val) : 0)->toArray();
        $drenaje = $historico->pluck('porcentaje_drenaje')->map(fn($val) => is_numeric($val) ? floatval($val) : 0)->toArray();
        $difCe   = $historico->pluck('diferencia_ce')->map(fn($val) => is_numeric($val) ? floatval($val) : 0)->toArray();
        $lux     = $historico->pluck('radiacion_lectura')->map(fn($val) => is_numeric($val) ? floatval($val) : 0)->toArray();

        // 4. Datos necesarios para alimentar los selectores
        $dueños = User::whereIn('rol', ['dueno', 'administrador', 'admin_general'])->orderBy('name')->get();
        
        $invernaderos = [];
        if ($request->filled('dueno_id')) {
            $invernaderos = SectorCaracteristica::where('user_id', $request->dueno_id)
                ->whereNotNull('invernadero')
                ->distinct()
                ->pluck('invernadero');
        }

        $sectores = [];
        if ($request->filled('dueno_id') && $request->filled('invernadero')) {
            $sectores = SectorCaracteristica::where('user_id', $request->dueno_id)
                ->where('invernadero', $request->invernadero)
                ->whereNotNull('sector')
                ->distinct()
                ->pluck('sector');
        }

        // Corrección segura para los meses disponibles
        $mesesDisponibles = MonitoreoClimaRiego::whereNotNull('fecha')
            ->selectRaw('DATE_FORMAT(fecha, "%Y-%m") as anio_mes')
            ->distinct()
            ->orderBy('anio_mes', 'desc')
            ->pluck('anio_mes');

        return view('graficas.index', compact('fechas', 'dpv', 'drenaje', 'difCe', 'lux', 'dueños', 'invernaderos', 'sectores', 'mesesDisponibles'));
    }

    private function enviarAlertaAdministradores($invernadero, $sector, $valor, $tipo = 'drenaje')
    {
        $admins = User::where('rol', 'administrador')
            ->whereNotNull('fcm_token')
            ->get();

        $projectId = "unitasrubraalertas";
        $ubicacion = "Invernadero " . ($invernadero ?? 'General') . " — Sector " . $sector;

        if ($tipo === 'temperatura') {
            $titulo = '⚠️ Alerta de Temperatura Crítica';
            $mensaje = "El " . $ubicacion . " registró una temperatura fuera de rango: " . $valor . "°C";
        } else {
            $titulo = '⚠️ Alerta de Drenaje en Hidroponía';
            $mensaje = "El " . $ubicacion . " registró un drenaje crítico de: " . $valor . "%";
        }

        foreach ($admins as $admin) {
            try {
                $jsonPath = storage_path('app/firebase-credentials.json');
                if (!file_exists($jsonPath)) {
                    \Illuminate\Support\Facades\Log::error("FCM Error: No se encontró el archivo firebase-credentials.json en storage/app/");
                    continue;
                }

                $jsonKey = json_decode(file_get_contents($jsonPath), true);

                $now = time();
                $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
                $payload = json_encode([
                    'iss' => $jsonKey['client_email'],
                    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                    'aud' => $jsonKey['token_uri'],
                    'iat' => $now,
                    'exp' => $now + 3600
                ]);

                $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
                $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

                $signature = '';
                openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $jsonKey['private_key'], OPENSSL_ALGO_SHA256);
                $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

                $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

                $ch = curl_init($jsonKey['token_uri']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt
                ]));
                $response = curl_exec($ch);
                curl_close($ch);

                $tokenData = json_decode($response, true);
                if (!isset($tokenData['access_token'])) {
                    \Illuminate\Support\Facades\Log::error("FCM Error: No se pudo obtener el access_token de Google. Respuesta: " . $response);
                    continue;
                }
                $accessToken = $tokenData['access_token'];

                $fcmPayload = [
                    'message' => [
                        'token' => $admin->fcm_token,
                        'notification' => [
                            'title' => $titulo,
                            'body' => $mensaje
                        ],
                        'android' => [
                            'priority' => 'HIGH',
                            'notification' => [
                                'sound' => 'default',
                                'default_sound' => true,
                                'default_vibrate_timings' => true
                            ]
                        ]
                    ]
                ];

                $ch = curl_init('https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send');
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmPayload));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $accessToken
                ]);

                $result = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($httpCode !== 200) {
                    \Illuminate\Support\Facades\Log::error("FCM Error HTTP $httpCode al enviar notificación al administrador ID {$admin->id}: " . $result);
                }

                curl_close($ch);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("FCM Exception: " . $e->getMessage());
                continue;
            }
        }
    }
}   